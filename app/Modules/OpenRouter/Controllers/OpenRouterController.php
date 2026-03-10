<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Controllers;

use App\Controllers\BaseController;
use App\Modules\OpenRouter\Libraries\OpenRouterService;
use App\Modules\OpenRouter\Libraries\OpenRouterPandocService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Parsedown;

/**
 * OpenRouterController — HTTP Orchestration for the OpenRouter module.
 *
 * Mirrors the Gemini module's controller structure:
 * - publicPage()       → Marketing landing page.
 * - index()            → Authenticated AI workspace.
 * - generate()         → Synchronous chat generation.
 * - stream()           → SSE streaming generation.
 * - updateSetting()    → Toggle assistant / stream mode.
 * - addPrompt()        → Save prompt template.
 * - deletePrompt()     → Remove prompt template.
 * - fetchHistory()     → Paginated history.
 * - deleteHistory()    → Remove history item.
 * - clearMemory()      → Wipe all user memory.
 * - downloadDocument() → Export AI output as PDF/DOCX.
 *
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 */
class OpenRouterController extends BaseController
{
    /**
     * @param OpenRouterService|null $openRouterService
     * @param OpenRouterPandocService|null $pandocService
     */
    public function __construct(
        protected ?OpenRouterService $openRouterService = null,
        protected ?OpenRouterPandocService $pandocService = null
    ) {
        $this->openRouterService = $openRouterService ?? service('openRouterService');
        $this->pandocService     = $pandocService ?? service('openRouterPandoc');
    }

    // --- Helper Methods ---

    /**
     * Prepares common context for both sync and stream interactions.
     *
     * @return array{userId: int, inputs: array, uploadedFileIds: array, model: string, userSetting: object|null, inputText: string}
     */
    private function _prepareInteractionContext(): array
    {
        $userId          = (int) session()->get('userId');
        $inputs          = $this->_validateGenerationRequest();
        $uploadedFileIds = $inputs['uploadedFileIds'] ?? [];

        if (isset($inputs['error'])) {
            return ['error' => $inputs['error']];
        }

        $userSetting = $this->openRouterService->getUserSettings($userId);
        $model       = $this->request->getGet('model') ?: ($this->request->getPost('model') ?: (session()->get('openrouter_model') ?: OpenRouterService::DEFAULT_MODEL));

        return [
            'userId'          => $userId,
            'inputs'          => $inputs,
            'uploadedFileIds' => $uploadedFileIds,
            'model'           => $model,
            'userSetting'     => $userSetting,
            'inputText'       => $inputs['inputText'],
        ];
    }

    /**
     * Validates generation request inputs.
     *
     * @return array Validated inputs or error.
     */
    private function _validateGenerationRequest(): array
    {
        if (!$this->validate(['prompt' => 'max_length[200000]'])) {
            return ['error' => 'Prompt is too long.'];
        }

        $inputText       = (string) $this->request->getPost('prompt');
        $uploadedFileIds = (array)  $this->request->getPost('uploaded_media');

        if (empty(trim($inputText)) && empty($uploadedFileIds)) {
            return ['error' => 'Please provide a prompt or attach a file.'];
        }

        return ['inputText' => $inputText, 'uploadedFileIds' => $uploadedFileIds];
    }

    /**
     * Returns a standardized success response (JSON for AJAX, redirect otherwise).
     *
     * @param string $message
     * @param array  $data
     * @return ResponseInterface|RedirectResponse
     */
    private function _respondSuccess(string $message, array $data = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(array_merge([
                'status'     => 'success',
                'message'    => $message,
                'csrf_token' => csrf_hash(),
            ], $data));
        }
        return redirect()->back()->with('success', $message);
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message
     * @param array  $errors
     * @return ResponseInterface|RedirectResponse
     */
    private function _respondError(string $message, array $errors = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => $message,
                'errors'     => $errors,
                'csrf_token' => csrf_hash(),
            ]);
        }
        return redirect()->back()->withInput()->with('error', $message);
    }


    /**
     * Sends an SSE error packet.
     *
     * @param string $msg
     * @return void
     */
    private function _sendSSEError(string $msg): void
    {
        // Session should already be closed by the caller, but ensure headers are set
        if (!headers_sent()) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
        }

        echo 'data: ' . json_encode(['error' => $msg, 'csrf_token' => csrf_hash()]) . "\n\n";
        if (ob_get_level() > 0) ob_flush();
        flush();
    }

    /**
     * Builds a standardized thinking block HTML (Gemini Parity).
     */
    private function _buildThinkingBlockHtml(string $thoughts): string
    {
        return sprintf(
            '<details class="thinking-block mb-3">' .
                '<summary class="cursor-pointer text-muted fw-bold small">Thinking Process</summary>' .
                '<div class="thinking-content fst-italic text-muted p-2 border-start mt-1 small">%s</div>' .
                '</details>',
            nl2br(esc($thoughts))
        );
    }

    /**
     * Parses markdown safely.
     *
     * @param string $text
     * @return string
     */
    private function _parseMarkdown(string $text): string
    {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $parsedown->setBreaksEnabled(true);
        return $parsedown->text($text);
    }

    // --- Public API ---

    /**
     * Renders the OpenRouter public marketing landing page.
     *
     * @return string
     */
    public function publicPage(): string
    {
        $data = [
            'pageTitle'       => 'OpenRouter AI | Access 100+ Models & Free LLMs | Afrikenkid',
            'metaDescription' => 'Access the world\'s best open-source and proprietary AI models through one seamless interface. Includes free high-speed models to start prompting immediately.',
            'canonicalUrl'    => url_to('openrouter.public'),
            'robotsTag'       => 'index, follow',
            'heroTitle'       => 'Multi-Model AI, Unified.',
            'heroSubtitle'    => 'Access the world\'s best open-source and proprietary AI models through one seamless interface. Intelligent conversations, fast responses, zero friction.'
        ];
        return view('App\Modules\OpenRouter\Views\openrouter\public_page', $data);
    }

    /**
     * Renders the authenticated AI workspace.
     *
     * @return string
     */
    public function index(): string
    {
        $userId      = (int) session()->get('userId');
        $prompts     = $this->openRouterService->getUserPrompts($userId);
        $userSetting = $this->openRouterService->getUserSettings($userId);

        $data = [
            'pageTitle'              => 'OpenRouter Workspace',
            'metaDescription'        => 'Your OpenRouter AI workspace.',
            'canonicalUrl'           => url_to('openrouter.index'),
            'robotsTag'              => 'noindex, follow',
            'result'                 => session()->getFlashdata('result'),
            'error'                  => session()->getFlashdata('error'),
            'prompts'                => $prompts,
            'assistant_mode_enabled' => $userSetting ? $userSetting->assistant_mode_enabled : true,
            'stream_output_enabled'  => $userSetting ? $userSetting->stream_output_enabled : false,
            'maxFileSize'            => OpenRouterService::MAX_FILE_SIZE,
            'maxFiles'               => OpenRouterService::MAX_FILES,
            'supportedMimeTypes'     => json_encode(OpenRouterService::SUPPORTED_MIME_TYPES),
            'recommendedModels'      => OpenRouterService::RECOMMENDED_MODELS,
            'currentModel'           => session()->get('openrouter_model') ?? OpenRouterService::DEFAULT_MODEL,
        ];

        return view('App\Modules\OpenRouter\Views\openrouter\query_form', $data);
    }

    /**
     * Processes a synchronous chat generation request.
     *
     * @return ResponseInterface|RedirectResponse
     */
    public function generate()
    {
        $ctx = $this->_prepareInteractionContext();
        if (isset($ctx['error'])) {
            return $this->_respondError($ctx['error']);
        }

        $options = [
            'assistant_mode'  => $ctx['userSetting'] ? $ctx['userSetting']->assistant_mode_enabled : true,
            'uploadedFileIds' => $ctx['uploadedFileIds'],
            'model'           => $ctx['model'],
        ];

        $result = $this->openRouterService->processInteraction($ctx['userId'], $ctx['inputText'], $options);

        // Always clean up temp files after request, success or failure
        $this->openRouterService->cleanupTempFiles($ctx['uploadedFileIds'], $ctx['userId']);

        if (isset($result['error'])) {
            log_message('error', "[OpenRouterController] Generation failed for User {$ctx['userId']}: " . $result['error']);
            return $this->_respondError($result['error']);
        }

        // Set success flash message for UI feedback (Gemini parity)
        if (isset($result['cost'])) {
            session()->setFlashdata('success', number_format($result['cost'], 6) . " credits deducted.");
        }

        $parsedHtml = $this->_parseMarkdown($result['result']);

        // Gemini Parity: Prepend thinking block if present
        if (!empty($result['thought'])) {
            $parsedHtml = $this->_buildThinkingBlockHtml($result['thought']) . "\n\n" . $parsedHtml;
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'               => 'success',
                'result'               => $parsedHtml,
                'raw_result'           => $result['result'],
                'thought'              => $result['thought'] ?? null,
                'flash_html'           => view('App\Views\partials\flash_messages'),
                'used_interaction_ids' => $result['used_interaction_ids'] ?? [],
                'new_interaction_id'   => $result['new_interaction_id'] ?? null,
                'timestamp'            => $result['timestamp'] ?? null,
                'user_input'           => $ctx['inputText'],
                'csrf_token'           => csrf_hash(),
            ]);
        }

        return redirect()->back()
            ->withInput()
            ->with('result', $parsedHtml)
            ->with('raw_result', $result['result']);
    }

    /**
     * Handles real-time text generation via SSE.
     *
     * @return ResponseInterface|void
     */
    public function stream()
    {
        $ctx = $this->_prepareInteractionContext();
        if (isset($ctx['error'])) {
            $this->_sendSSEError($ctx['error']);
            return $this->response;
        }

        $options = [
            'assistant_mode'  => $ctx['userSetting'] ? $ctx['userSetting']->assistant_mode_enabled : true,
            'uploadedFileIds' => $ctx['uploadedFileIds'],
        ];

        // Prepare context (this would involve reading files if needed)
        $prep = $this->openRouterService->prepareStreamContext($ctx['userId'], $ctx['inputText'], $options);

        // Clinerules: Close session before streaming to prevent locking
        session_write_close();

        // Clinerules: Set correct headers for SSE
        $this->response->setHeader('Content-Type', 'text/event-stream');
        $this->response->setHeader('Cache-Control', 'no-cache');
        $this->response->setHeader('Connection', 'keep-alive');
        $this->response->setHeader('X-Accel-Buffering', 'no'); // For Nginx
        $this->response->sendHeaders();

        if (ob_get_level() > 0) ob_end_flush();

        if (isset($prep['error'])) {
            $this->_sendSSEError($prep['error']);
            return $this->response;
        }

        // Send initial CSRF token (Clinerules)
        echo 'data: ' . json_encode(['csrf_token' => csrf_hash()]) . "\n\n";
        flush();

        $this->openRouterService->generateStream(
            $prep['messages'],
            $ctx['model'],
            // Chunk callback
            function ($chunk) use ($ctx) {
                if (is_array($chunk) && isset($chunk['error'])) {
                    echo 'data: ' . json_encode(['error' => $chunk['error'], 'csrf_token' => csrf_hash()]) . "\n\n";
                } elseif (is_array($chunk) && isset($chunk['thought'])) {
                    echo 'data: ' . json_encode(['thought' => $chunk['thought'], 'csrf_token' => csrf_hash()]) . "\n\n";
                } elseif (is_array($chunk) && isset($chunk['usage'])) {
                    echo 'data: ' . json_encode(['usage' => $chunk['usage'], 'csrf_token' => csrf_hash()]) . "\n\n";
                } else {
                    echo 'data: ' . json_encode(['text' => $chunk]) . "\n\n";
                }
                if (ob_get_level() > 0) ob_flush();
                flush();
            },
            // Complete callback
            function ($fullText, $usage = []) use ($ctx, $prep) {
                $result = $this->openRouterService->finalizeStreamInteraction($ctx['userId'], $ctx['inputText'], $fullText, $prep['contextData'], $usage);

                // Cleanup temp files
                $this->openRouterService->cleanupTempFiles($ctx['uploadedFileIds'], $ctx['userId']);

                $finalPayload = [
                    'csrf_token'           => csrf_hash(),
                    'used_interaction_ids' => $result['used_interaction_ids'] ?? [],
                    'new_interaction_id'   => $result['new_interaction_id'] ?? null,
                    'timestamp'            => $result['timestamp'] ?? null,
                    'user_input'           => $ctx['inputText'],
                ];

                if (isset($result['cost'])) {
                    session()->setFlashdata('success', number_format($result['cost'], 6) . " credits deducted.");
                    $finalPayload['flash_html'] = view('App\Views\partials\flash_messages');
                }

                echo "event: close\n";
                echo 'data: ' . json_encode($finalPayload) . "\n\n";

                if (ob_get_level() > 0) ob_flush();
                flush();
            }
        );

        exit;
    }

    /**
     * Updates a user setting.
     *
     * @return ResponseInterface
     */
    public function updateSetting(): ResponseInterface
    {
        $userId = (int) session()->get('userId');
        if ($userId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Auth required.', 'csrf_token' => csrf_hash()]);
        }

        $key = $this->request->getPost('setting_key');

        return match ($key) {
            'assistant_mode_enabled', 'stream_output_enabled' => $this->_handleBooleanSetting($userId, $key),
            'openrouter_model' => $this->_handleModelSetting(),
            default => $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid setting.', 'csrf_token' => csrf_hash()])
        };
    }

    /**
     * Helper to handle boolean setting toggles.
     */
    private function _handleBooleanSetting(int $userId, string $key): ResponseInterface
    {
        $input = $this->request->getPost('enabled');
        // Flexible parsing: handles 'true', '1', true, 1
        $value = filter_var($input, FILTER_VALIDATE_BOOLEAN);

        $this->openRouterService->updateUserSetting($userId, $key, $value);
        return $this->response->setJSON(['status' => 'success', 'csrf_token' => csrf_hash()]);
    }

    /**
     * Helper to handle model selection setting.
     */
    private function _handleModelSetting(): ResponseInterface
    {
        $model = $this->request->getPost('model_id');
        if ($model) {
            session()->set('openrouter_model', $model);
            return $this->response->setJSON(['status' => 'success', 'csrf_token' => csrf_hash()]);
        }
        return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Model required.', 'csrf_token' => csrf_hash()]);
    }

    /**
     * Saves a new prompt template.
     *
     * @return ResponseInterface|RedirectResponse
     */
    public function addPrompt()
    {
        $userId = (int) session()->get('userId');
        $rules  = ['title' => 'required|max_length[255]', 'prompt_text' => 'required'];

        if (!$this->validate($rules)) {
            return $this->_respondError('Invalid input.', $this->validator->getErrors());
        }

        $id = $this->openRouterService->addPrompt($userId, [
            'title'       => $this->request->getPost('title'),
            'prompt_text' => $this->request->getPost('prompt_text'),
        ]);

        return $this->_respondSuccess('Prompt saved.', [
            'prompt' => [
                'id'          => $id,
                'title'       => $this->request->getPost('title'),
                'prompt_text' => $this->request->getPost('prompt_text'),
            ]
        ]);
    }

    /**
     * Removes a saved prompt template.
     *
     * @param int $id
     * @return ResponseInterface|RedirectResponse
     */
    public function deletePrompt(int $id)
    {
        $userId = (int) session()->get('userId');
        if ($this->openRouterService->deletePrompt($userId, $id)) {
            return $this->_respondSuccess('Prompt deleted.');
        }
        return $this->_respondError('Unauthorized or not found.');
    }

    /**
     * Clears all conversational memory for the user.
     *
     * @return RedirectResponse
     */
    public function clearMemory(): RedirectResponse
    {
        $userId  = (int) session()->get('userId');
        $success = $this->openRouterService->clearUserMemory($userId);
        return redirect()->back()->with($success ? 'success' : 'error', $success ? 'Memory cleared.' : 'Failed to clear memory.');
    }

    /**
     * Returns paginated interaction history.
     *
     * @return ResponseInterface
     */
    public function fetchHistory(): ResponseInterface
    {
        $userId = (int) session()->get('userId');
        $limit  = (int) ($this->request->getVar('limit') ?? 20);
        $offset = (int) ($this->request->getVar('offset') ?? 0);

        return $this->response->setJSON([
            'status'     => 'success',
            'history'    => $this->openRouterService->getUserHistory($userId, $limit, $offset),
            'csrf_token' => csrf_hash(),
        ]);
    }

    /**
     * Deletes a single history interaction.
     *
     * @return ResponseInterface
     */
    public function deleteHistory(): ResponseInterface
    {
        $userId   = (int) session()->get('userId');
        $uniqueId = $this->request->getPost('unique_id');

        if (!$uniqueId) {
            return $this->_respondError('Invalid ID.');
        }

        if ($this->openRouterService->deleteUserInteraction($userId, $uniqueId)) {
            return $this->response->setJSON(['status' => 'success', 'csrf_token' => csrf_hash()]);
        }
        return $this->_respondError('Failed to delete.');
    }

    /**
     * Handles AJAX file upload for context attachment.
     *
     * Stores the file temporarily and returns an ID to the frontend.
     * The Unlink Pattern (clinerules §3.3) is applied: generate() cleans up after use.
     *
     * @return ResponseInterface JSON file metadata.
     */
    public function uploadMedia(): ResponseInterface
    {
        $userId = (int) session()->get('userId');
        if ($userId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Auth required.', 'csrf_token' => csrf_hash()]);
        }

        if (!$this->validate([
            'file' => [
                'label' => 'File',
                'rules' => 'uploaded[file]|max_size[file,' . (OpenRouterService::MAX_FILE_SIZE / 1024) . ']|mime_in[file,' . implode(',', OpenRouterService::SUPPORTED_MIME_TYPES) . ']',
            ],
        ])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'     => 'error',
                'message'    => $this->validator->getErrors()['file'] ?? 'Invalid file.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $file   = $this->request->getFile('file');
        $result = $this->openRouterService->storeTempMedia($file, $userId);

        if (!$result['status']) {
            log_message('error', "[OpenRouterController] Upload failed for User {$userId}: " . ($result['error'] ?? 'Unknown'));
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'File save failed.', 'csrf_token' => csrf_hash()]);
        }

        return $this->response->setJSON([
            'status'        => 'success',
            'file_id'       => $result['filename'],
            'original_name' => $result['original_name'],
            'csrf_token'    => csrf_hash(),
        ]);
    }

    /**
     * Removes a temporary uploaded file.
     *
     * @return ResponseInterface JSON status.
     */
    public function deleteMedia(): ResponseInterface
    {
        $userId = (int) session()->get('userId');
        if ($userId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'csrf_token' => csrf_hash()]);
        }

        $fileId = $this->request->getPost('file_id');
        if (!$fileId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing file_id.', 'csrf_token' => csrf_hash()]);
        }

        if ($this->openRouterService->deleteTempMedia($userId, $fileId)) {
            return $this->response->setJSON(['status' => 'success', 'csrf_token' => csrf_hash()]);
        }

        return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'File not found.', 'csrf_token' => csrf_hash()]);
    }

    /**
     * Converts the current AI output into a downloadable document.
     *
     * Implements the Unlink Pattern (clinerules §3.3): generate → stream → delete.
     *
     * @return ResponseInterface|RedirectResponse Binary file or redirect on error.
     */
    public function downloadDocument()
    {
        $userId = (int) session()->get('userId');

        if (!$this->validate([
            'raw_response' => 'required',
            'format'       => 'required|in_list[pdf,docx]',
        ])) {
            log_message('error', "[OpenRouterController] Document validation failed for User {$userId}.");
            return redirect()->back()->with('error', 'Invalid document request.');
        }

        $content = $this->request->getPost('raw_response');
        $format  = $this->request->getPost('format');

        $result = $this->openRouterService->generateDocument($content, $format);

        if ($result['status'] === 'success') {
            $mime     = $format === 'docx'
                ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                : 'application/pdf';
            $filename = 'OpenRouter-Output-' . date('Ymd-His') . '.' . $format;

            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($result['fileData']);
        }

        $errorMsg = $result['message'] ?? 'Document generation failed.';
        log_message('error', "[OpenRouterController] Document generation failed for User {$userId}: {$errorMsg}");
        return redirect()->back()->with('error', $errorMsg);
    }
}
