<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Libraries;

use App\Models\UserModel;
use App\Modules\OpenRouter\Models\OpenRouterPromptModel;
use App\Modules\OpenRouter\Models\OpenRouterUserSettingsModel;
use CodeIgniter\Config\Services;

/**
 * OpenRouterService — Core AI orchestration for OpenRouter.
 *
 * Responsibilities:
 * - Synchronous chat completions (processInteraction).
 * - Real-time SSE streaming (generateStream).
 * - Transactional balance deduction.
 * - Conversational memory via OpenRouterMemoryService facade.
 * - Prompt template CRUD.
 * - User settings management.
 */
class OpenRouterService
{
    public const API_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /** Default Model (Used if user has no preferred model) */
    public const DEFAULT_MODEL = 'stepfun/step-3.5-flash:free';

    /** Recommended models for the selector */
    public const RECOMMENDED_MODELS = [
        'anthropic/claude-3.5-sonnet'      => 'Claude 3.5 Sonnet',
        'anthropic/claude-3-opus'          => 'Claude 3 Opus',
        'openai/gpt-4o'                    => 'GPT-4o',
        'openai/gpt-4o-mini'               => 'GPT-4o Mini',
        'meta-llama/llama-3.1-70b-instruct' => 'Llama 3.1 70B',
        'mistralai/mistral-large'           => 'Mistral Large',
        'stepfun/step-3.5-flash:free'       => 'step-3.5-flash:free',
        'google/gemma-3-27b-it:free'        => 'Gemma 3.27B (Free)',
        'google/gemma-3-12b-it:free'        => 'Gemma 3.12B (Free)',
    ];

    /**
     * @param string|null                    $apiKey
     * @param UserModel|null                 $userModel
     * @param mixed                          $db
     * @param OpenRouterPromptModel|null     $promptModel
     * @param OpenRouterUserSettingsModel|null $userSettingsModel
     */
    public function __construct(
        protected ?string $apiKey = null,
        protected ?UserModel $userModel = null,
        protected $db = null,
        protected ?OpenRouterPromptModel $promptModel = null,
        protected ?OpenRouterUserSettingsModel $userSettingsModel = null
    ) {
        $this->apiKey            = $apiKey ?? env('OPENROUTER_API_KEY');
        $this->userModel         = $userModel ?? new UserModel();
        $this->db                = $db ?? \Config\Database::connect();
        $this->promptModel       = $promptModel ?? new OpenRouterPromptModel();
        $this->userSettingsModel = $userSettingsModel ?? new OpenRouterUserSettingsModel();
    }

    // --- Helper Methods ---

    /**
     * Executes a synchronous HTTP request to the OpenRouter API.
     *
     * @param array $messages Chat messages array.
     * @return array Decoded API response or error.
     */
    private function _executeRequest(array $messages, string $model = self::DEFAULT_MODEL): array
    {
        if (!$this->apiKey) {
            return ['error' => 'OpenRouter API Key is missing.'];
        }

        $payloadData = [
            'model'    => $model,
            'messages' => $messages,
        ];

        if ($this->_containsPdf($messages)) {
            $payloadData['plugins'] = [
                [
                    'id'  => 'file-parser',
                    'pdf' => ['engine' => 'pdf-text'],
                ]
            ];
        }

        $payload = json_encode($payloadData);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $this->_getHeaders(),
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($response === false) {
            log_message('error', '[OpenRouterService] cURL Error: ' . $error);
            return ['error' => 'Could not connect to OpenRouter: ' . $error, 'http_code' => 0];
        }

        $data = json_decode($response, true);

        if ($statusCode === 200) {
            $content   = $data['choices'][0]['message']['content'] ?? '';
            $reasoning = $data['choices'][0]['message']['reasoning_content'] ?? ($data['choices'][0]['message']['reasoning'] ?? null);

            return [
                'result'    => $content,
                'thought'   => $reasoning,
                'usage'     => $data['usage'] ?? [],
                'raw'       => $data,
                'http_code' => 200,
            ];
        }

        $msg = $data['error']['message'] ?? "API Error {$statusCode}";
        log_message('error', "[OpenRouterService] HTTP {$statusCode}: {$msg}. Response: " . $response);
        return ['error' => $msg, 'http_code' => $statusCode];
    }

    /**
     * Deducts a flat cost from the user's balance atomically.
     *
     * @param int   $userId
     * @param float $cost
     * @return void
     */
    private function _deductCost(int $userId, float $cost): void
    {
        $this->userModel->deductBalance($userId, number_format($cost, 4, '.', ''), true);
    }

    /**
     * Standardizes OpenRouter headers for identification and rankings.
     *
     * @return array
     */
    private function _getHeaders(): array
    {
        return [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: ' . base_url(),
            'X-Title: OpenRouter AI Studio',
        ];
    }

    /**
     * Extracts text chunks from the raw SSE buffer.
     *
     * Leverages a rolling buffer to handle partial data fragments across chunks.
     *
     * @param string $buffer Rolling stream buffer (modified in-place).
     * @return array Processed text chunks.
     */
    private function _processStreamBuffer(string &$buffer): array
    {
        $chunks = [];
        $lines  = explode("\n", $buffer);

        // The last element is inherently the "remainder" of the split (may be empty or an incomplete chunk)
        $buffer = array_pop($lines);

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || $line === 'data: [DONE]') {
                continue;
            }

            // OpenRouter Keep-Alive packets
            if (str_starts_with($line, ':')) {
                log_message('debug', "[OpenRouterService] Stream Heartbeat: {$line}");
                continue;
            }

            if (str_starts_with($line, 'data: ')) {
                $json = substr($line, 6);
                $data = json_decode($json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    // Check for usage (usually in the final chunk)
                    if (isset($data['usage'])) {
                        $chunks[] = ['usage' => $data['usage']];
                    }

                    // Check for deltas in choices
                    $delta = $data['choices'][0]['delta'] ?? null;
                    if ($delta) {
                        // Prioritize Reasoning (Thinking Blocks)
                        if (isset($delta['reasoning_content'])) {
                            $chunks[] = ['thought' => $delta['reasoning_content']];
                        } elseif (isset($delta['reasoning'])) {
                            $chunks[] = ['thought' => $delta['reasoning']];
                        }

                        // Then standard content
                        if (isset($delta['content']) && $delta['content'] !== '') {
                            $chunks[] = $delta['content'];
                        }
                    }

                    if (isset($data['error']['message'])) {
                        $chunks[] = ['error' => $data['error']['message']];
                    }
                } else {
                    log_message('debug', "[OpenRouterService] Stream JSON parse skipped. JSON: " . $json . " Error: " . json_last_error_msg());
                }
            } else {
                log_message('debug', "[OpenRouterService] Unexpected stream line: " . $line);
            }
        }

        return $chunks;
    }

    /**
     * Checks if any message in the array contains a PDF file part.
     *
     * @param array $messages
     * @return bool
     */
    private function _containsPdf(array $messages): bool
    {
        foreach ($messages as $msg) {
            if (isset($msg['content']) && is_array($msg['content'])) {
                foreach ($msg['content'] as $part) {
                    if (isset($part['type']) && $part['type'] === 'file' && isset($part['file']['file_data']) && str_contains($part['file']['file_data'], 'application/pdf')) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Prepares conversational context and multimodal messages.
     *
     * @param int    $userId
     * @param string $prompt
     * @param array  $options
     * @return array{messages?: array, contextData?: array, error?: string}
     */
    private function _prepareContextAndMessages(int $userId, string $prompt, array $options): array
    {
        $isAssistantMode = $options['assistant_mode'] ?? true;
        $contextData     = ['usedInteractionIds' => [], 'memoryService' => null];

        if ($isAssistantMode) {
            $memoryService = service('openRouterMemory', $userId);
            $contextData   = $memoryService->buildContextualPrompt($prompt);
            $userContent   = $contextData['finalPrompt'];
        } else {
            $userContent   = $prompt;
        }

        // Handle File Context
        $uploadedFileIds = $options['uploadedFileIds'] ?? [];
        if (!empty($uploadedFileIds)) {
            $fileResult = $this->prepareUploadedFiles($uploadedFileIds, $userId);
            if (isset($fileResult['error'])) {
                return ['error' => $fileResult['error']];
            }

            // Build multi-part content: Text MUST come first for best compatibility/perception
            $contentParts = [['type' => 'text', 'text' => $userContent]];
            $contentParts = array_merge($contentParts, $fileResult['parts']);

            $messages = [['role' => 'user', 'content' => $contentParts]];
        } else {
            $messages = [['role' => 'user', 'content' => $userContent]];
        }

        return [
            'messages'    => $messages,
            'contextData' => $contextData,
        ];
    }

    // --- Public API ---

    /**
     * Processes a synchronous user-AI interaction.
     *
     * Workflow:
     * 1. Build context from memory (if assistant mode).
     * 2. Call the API.
     * 3. Atomically deduct cost and save interaction.
     *
     * @param int    $userId
     * @param string $prompt
     * @param array  $options  ['assistant_mode' => bool]
     * @return array Result or error.
     */
    public function processInteraction(int $userId, string $prompt, array $options = []): array
    {
        $prep = $this->_prepareContextAndMessages($userId, $prompt, $options);
        if (isset($prep['error'])) {
            return ['error' => $prep['error']];
        }

        $messages    = $prep['messages'];
        $contextData = $prep['contextData'];
        $isAssistantMode = $options['assistant_mode'] ?? true;
        $model           = $options['model'] ?? self::DEFAULT_MODEL;

        $apiResponse = $this->_executeRequest($messages, $model);

        if (isset($apiResponse['error'])) {
            return ['error' => $apiResponse['error']];
        }

        // Atomic billing + persistence
        $cost = (float) ($apiResponse['usage']['cost'] ?? 0.0);
        $this->db->transStart();
        if ($cost > 0) {
            log_message('info', "[OpenRouterService] Deducting cost: {$cost} for User: {$userId}");
            $this->_deductCost($userId, $cost);
        }

        $memoryResult = [];
        if ($isAssistantMode && isset($contextData['memoryService'])) {
            $memoryResult = $contextData['memoryService']->saveInteraction(
                $prompt,
                $apiResponse['result'],
                $contextData['usedInteractionIds']
            );
        }
        $this->db->transComplete();

        return [
            'result'               => $apiResponse['result'],
            'thought'              => $apiResponse['thought'] ?? null,
            'cost'                 => $cost,
            'used_interaction_ids' => $contextData['usedInteractionIds'],
            'new_interaction_id'   => $memoryResult['id'] ?? null,
            'timestamp'            => $memoryResult['timestamp'] ?? null,
            'success'              => true,
        ];
    }

    /**
     * Initiates a real-time SSE stream to the OpenRouter API.
     *
     * @param array    $messages         Pre-built messages array.
     * @param callable $chunkCallback    Invoked for each decoded text chunk.
     * @param callable $completeCallback Invoked when the stream ends.
     * @return void
     */
    public function generateStream(array $messages, string $model, callable $chunkCallback, callable $completeCallback): void
    {
        if (!$this->apiKey) {
            $chunkCallback(['error' => 'API Key missing.']);
            return;
        }

        $payloadData = [
            'model'    => $model ?: self::DEFAULT_MODEL,
            'messages' => $messages,
            'stream'   => true,
        ];

        if ($this->_containsPdf($messages)) {
            $payloadData['plugins'] = [
                [
                    'id'  => 'file-parser',
                    'pdf' => ['engine' => 'pdf-text'],
                ]
            ];
        }

        $payload = json_encode($payloadData);
        $fullText = '';
        $usage    = [];
        $buffer   = '';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL       => self::API_URL,
            CURLOPT_POST      => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $this->_getHeaders(),
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buffer, &$fullText, &$usage, $chunkCallback) {
                $buffer .= $chunk;
                $parsedChunks = $this->_processStreamBuffer($buffer);
                foreach ($parsedChunks as $result) {
                    if (is_array($result) && isset($result['error'])) {
                        $chunkCallback($result); // Pass error array through
                    } elseif (is_array($result) && isset($result['thought'])) {
                        // Pass along thought chunks without adding to fullText
                        $chunkCallback($result);
                    } elseif (is_array($result) && isset($result['usage'])) {
                        $usage = $result['usage'];
                        $chunkCallback($result);
                    } else {
                        $fullText .= $result;
                        $chunkCallback($result);
                    }
                }
                return strlen($chunk);
            }
        ]);

        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);

        if ($code === 200) {
            $completeCallback($fullText, $usage);
            return;
        }

        log_message('warning', "[OpenRouterService] Stream Error HTTP {$code}. Curl: {$err}");
        $chunkCallback(['error' => "Stream Error: HTTP {$code}."]);
    }

    /**
     * Prepares context and validates balance before streaming.
     *
     * @param int    $userId
     * @param string $prompt
     * @param array  $options
     * @return array Pre-calculated messages and context data, or error.
     */
    public function prepareStreamContext(int $userId, string $prompt, array $options = []): array
    {
        return $this->_prepareContextAndMessages($userId, $prompt, $options);
    }

    /**
     * Finalizes a streaming interaction: saves memory.
     *
     * @param int    $userId
     * @param string $inputText
     * @param string $fullText
     * @param array  $contextData
     * @return array Result metadata.
     */
    public function finalizeStreamInteraction(int $userId, string $inputText, string $fullText, array $contextData, array $usage = []): array
    {
        $memoryResult = [];
        $isAssistantMode = isset($contextData['memoryService']) && $contextData['memoryService'] !== null;

        $cost = (float) ($usage['cost'] ?? 0.0);

        if ($isAssistantMode || $cost > 0) {
            $this->db->transStart();

            if ($cost > 0) {
                log_message('info', "[OpenRouterService] Deducting stream cost: {$cost} for User: {$userId}");
                $this->_deductCost($userId, $cost);
            }

            if ($isAssistantMode) {
                $memoryResult = $contextData['memoryService']->saveInteraction(
                    $inputText,
                    $fullText,
                    $contextData['usedInteractionIds'] ?? []
                );
            }

            $this->db->transComplete();
        }

        return [
            'cost'                 => $cost,
            'used_interaction_ids' => $contextData['usedInteractionIds'] ?? [],
            'new_interaction_id'   => $memoryResult['id'] ?? null,
            'timestamp'            => $memoryResult['timestamp'] ?? null,
        ];
    }

    // --- User Settings ---

    /**
     * Returns the user's OpenRouter settings.
     *
     * @param int $userId
     * @return \App\Modules\OpenRouter\Entities\OpenRouterUserSetting|null
     */
    public function getUserSettings(int $userId)
    {
        return $this->userSettingsModel->where('user_id', $userId)->first();
    }

    /**
     * Updates a single boolean setting key for a user.
     *
     * @param int    $userId
     * @param string $key
     * @param bool   $value
     * @return void
     */
    public function updateUserSetting(int $userId, string $key, bool $value): void
    {
        $setting = $this->getUserSettings($userId);
        if ($setting) {
            $this->userSettingsModel->update($setting->id, [$key => $value]);
            return;
        }
        $this->userSettingsModel->save(['user_id' => $userId, $key => $value]);
    }

    // --- Prompt Management ---

    /**
     * Returns all saved prompts for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserPrompts(int $userId): array
    {
        return $this->promptModel->where('user_id', $userId)->findAll();
    }

    /**
     * Saves a new prompt template.
     *
     * @param int   $userId
     * @param array $data
     * @return int|string|false
     */
    public function addPrompt(int $userId, array $data)
    {
        return $this->promptModel->insert([
            'user_id'     => $userId,
            'title'       => $data['title'],
            'prompt_text' => $data['prompt_text'],
        ]);
    }

    /**
     * Deletes a prompt template, verifying ownership.
     *
     * @param int $userId
     * @param int $promptId
     * @return bool
     */
    public function deletePrompt(int $userId, int $promptId): bool
    {
        /** @var \App\Modules\OpenRouter\Entities\OpenRouterPrompt|null $prompt */
        $prompt = $this->promptModel->find($promptId);
        if ($prompt && $prompt->user_id == $userId) {
            return $this->promptModel->delete($promptId);
        }
        return false;
    }

    // --- History & Memory ---

    /**
     * Returns paginated interaction history via the memory service facade.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserHistory(int $userId, int $limit = 20, int $offset = 0): array
    {
        $memoryService = service('openRouterMemory', $userId);
        return $memoryService->getUserHistory($userId, $limit, $offset);
    }

    /**
     * Deletes a single interaction record.
     *
     * @param int    $userId
     * @param string $uniqueId
     * @return bool
     */
    public function deleteUserInteraction(int $userId, string $uniqueId): bool
    {
        $memoryService = service('openRouterMemory', $userId);
        return $memoryService->deleteInteraction($userId, $uniqueId);
    }

    /**
     * Clears all memory for a user.
     *
     * @param int $userId
     * @return bool
     */
    public function clearUserMemory(int $userId): bool
    {
        $memoryService = service('openRouterMemory', $userId);
        return $memoryService->clearAll($userId);
    }

    // --- Media Storage (Stateless/Tempfile Pattern) ---

    /**
     * Supported MIME types for uploaded context files.
     */
    public const SUPPORTED_MIME_TYPES = [
        'text/plain',
        'application/pdf',
        'text/csv',
        'text/html',
        'text/markdown',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /** Maximum file size (10 MB in bytes). */
    public const MAX_FILE_SIZE = 10485760;

    /** Maximum number of files allowed for context. */
    public const MAX_FILES = 10;

    /**
     * Stores an uploaded file in the user's ephemeral temp directory.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param int $userId
     * @return array{status: bool, filename?: string, original_name?: string, error?: string}
     */
    public function storeTempMedia($file, int $userId): array
    {
        $userTempPath = WRITEPATH . 'uploads/openrouter_temp/' . $userId . '/';

        if (!is_dir($userTempPath)) {
            if (!mkdir($userTempPath, 0755, true)) {
                return ['status' => false, 'error' => 'Failed to create upload directory.'];
            }
        }

        $fileName = $file->getRandomName();
        $originalName = $file->getClientName();
        $mimeType = $file->getMimeType();

        if (!$file->move($userTempPath, $fileName)) {
            return ['status' => false, 'error' => $file->getErrorString()];
        }

        // Save metadata to preserve original filename
        file_put_contents($userTempPath . $fileName . '.meta', json_encode([
            'original_name' => $originalName,
            'mime_type'     => $mimeType,
        ]));

        return ['status' => true, 'filename' => $fileName, 'original_name' => $originalName];
    }

    /**
     * Deletes a single temporary file.
     *
     * @param int    $userId
     * @param string $fileId
     * @return bool
     */
    public function deleteTempMedia(int $userId, string $fileId): bool
    {
        $filePath = WRITEPATH . 'uploads/openrouter_temp/' . $userId . '/' . basename($fileId);
        if (file_exists($filePath)) {
            @unlink($filePath . '.meta');
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Cleans up temporary files after a request is processed.
     *
     * @param array $fileIds
     * @param int   $userId
     * @return void
     */
    public function cleanupTempFiles(array $fileIds, int $userId): void
    {
        foreach ($fileIds as $fileId) {
            if (!$this->deleteTempMedia($userId, $fileId)) {
                $filePath = WRITEPATH . 'uploads/openrouter_temp/' . $userId . '/' . basename($fileId);
                log_message('error', "[OpenRouterService] Failed to clean up temporary file: {$filePath}");
            }
        }
    }

    /**
     * Reads uploaded context files and returns their content as text messages.
     *
     * Converts files to base64 inline data parts for inclusion in the API message.
     *
     * @param array $fileIds
     * @param int   $userId
     * @return array{parts?: array, error?: string}
     */
    public function prepareUploadedFiles(array $fileIds, int $userId): array
    {
        $parts        = [];
        $userTempPath = WRITEPATH . 'uploads/openrouter_temp/' . $userId . '/';

        foreach ($fileIds as $fileId) {
            $filePath = $userTempPath . basename($fileId);
            $metaPath = $filePath . '.meta';

            if (!file_exists($filePath)) {
                continue; // Skip silently if cleared
            }

            $meta = [];
            if (file_exists($metaPath)) {
                $meta = json_decode(file_get_contents($metaPath), true) ?: [];
            }

            $mimeType = $meta['mime_type'] ?? (mime_content_type($filePath) ?: 'application/octet-stream');
            $originalName = $meta['original_name'] ?? basename($fileId);

            // Map types according to OpenRouter Multimodal Docs
            match (true) {
                str_starts_with($mimeType, 'image/') => $parts[] = [
                    'type'      => 'image_url',
                    'image_url' => ['url' => 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($filePath))]
                ],
                $mimeType === 'application/pdf' => $parts[] = [
                    'type' => 'file',
                    'file' => [
                        'filename'  => $originalName,
                        'file_data' => 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($filePath))
                    ]
                ],
                str_starts_with($mimeType, 'audio/') => $parts[] = [
                    'type'        => 'input_audio',
                    'input_audio' => [
                        'data'   => base64_encode(file_get_contents($filePath)),
                        'format' => str_replace('audio/', '', $mimeType) ?: 'mp3'
                    ]
                ],
                str_starts_with($mimeType, 'video/') => $parts[] = [
                    'type'      => 'video_url',
                    'video_url' => ['url' => 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($filePath))]
                ],
                str_starts_with($mimeType, 'text/') || in_array($mimeType, ['application/json', 'text/csv', 'application/javascript']) => $parts[] = [
                    'type' => 'text',
                    'text' => "[Context File: " . $originalName . "]\n\n" . file_get_contents($filePath) . "\n---"
                ],
                default => (filesize($filePath) < 1024 * 1024) ? $parts[] = [
                    'type' => 'text',
                    'text' => "[Attached File: " . $originalName . "]\n\n" . file_get_contents($filePath) . "\n---"
                ] : null
            };
        }

        return ['parts' => $parts];
    }

    // --- Document Generation ---

    /**
     * Generates a downloadable document from AI output.
     *
     * Uses the OpenRouter module's own DocumentService — no external dependencies.
     *
     * @param string $markdownContent
     * @param string $format          'pdf' or 'docx'
     * @return array{status: string, fileData?: string, message?: string}
     */
    public function generateDocument(string $markdownContent, string $format): array
    {
        $documentService = service('openRouterDocumentService');
        return $documentService->generate($markdownContent, $format, [
            'creator' => 'OpenRouter AI Studio',
        ]);
    }
}
