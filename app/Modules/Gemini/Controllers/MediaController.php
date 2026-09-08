<?php

declare(strict_types=1);

namespace App\Modules\Gemini\Controllers;

use App\Controllers\BaseController;
use App\Modules\Gemini\Libraries\MediaGenerationService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class MediaController
 *
 * Manages media generation lifecycle (Images and Videos).
 *
 * @package App\Modules\Gemini\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property IncomingRequest $request
 */
class MediaController extends BaseController
{
    use ResponseTrait;

    /**
     * Injected Media Generation Service instance.
     *
     * @var MediaGenerationService
     */
    protected MediaGenerationService $mediaService;

    /**
     * Initializes the controller with its dependencies resolved via the Service Container.
     */
    public function __construct()
    {
        /** @var MediaGenerationService $service */
        $service = service('mediaGenerationService');
        $this->mediaService = $service;
    }

    // --- Helper Methods ---

    /**
     * Reports processing errors via JSON or context-aware redirect.
     *
     * @param string              $message Descriptive error detail.
     * @param int                 $code    HTTP status code.
     * @param array<string, mixed> $data    Contextual metadata for frontend error handling.
     *
     * @return ResponseInterface|RedirectResponse Structured error response.
     */
    private function _respondError(string $message, int $code = 400, array $data = []): ResponseInterface|RedirectResponse
    {
        if ($this->request->isAJAX()) {
            return $this->respond(array_merge([
                'status'     => 'error',
                'message'    => $message,
                'csrf_token' => csrf_hash(),
            ], $data), $code);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }

    /**
     * Filters media URLs to prevent Cross-Site Scripting (XSS).
     *
     * Implementation details:
     * - Whitelists protocols (http, https, data).
     * - Reconstructs URL components to strip malicious payloads.
     * - HTML-escapes attributes for safe injection into View templates.
     *
     * @param string $url Source URI from generation providers.
     * @return string Validated and attribute-safe URI.
     * @throws \RuntimeException If protocol violates security policy.
     */
    private function _sanitizeMediaUrl(string $url): string
    {
        // Parse URL
        $parsed = parse_url($url);

        // Whitelist protocols (data: for inline images, http/https for external)
        $allowedProtocols = ['http', 'https', 'data'];
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], $allowedProtocols, true)) {
            log_message('warning', '[MediaController] Blocked non-whitelisted URL protocol: ' . ($parsed['scheme'] ?? 'none'));
            throw new \RuntimeException('Invalid media URL protocol');
        }

        // For data URIs, just escape and return (already embedded)
        if ($parsed['scheme'] === 'data') {
            return esc($url, 'attr');
        }

        // Rebuild URL to strip potentially malicious query params or fragments
        $sanitized = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) $sanitized .= ':' . $parsed['port'];
        if (isset($parsed['path'])) $sanitized .= $parsed['path'];
        if (isset($parsed['query'])) $sanitized .= '?' . $parsed['query'];

        // HTML escape for attribute safety
        return esc($sanitized, 'attr');
    }


    /**
     * Orchestrates the media generation workflow.
     *
     * Performs multimodal part construction, validates provider configs,
     * and processes financial deductions upon successful job initiation.
     *
     * @return ResponseInterface|RedirectResponse Structured generation results.
     */
    public function generate(): ResponseInterface|RedirectResponse
    {
        $rules = [
            'prompt'   => 'required|min_length[3]|max_length[1000]',
            'model_id' => 'required',
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $msg    = implode('. ', $errors);

            return $this->_respondError($msg, 400, ['errors' => $errors]);
        }

        $userId          = (int) session()->get('userId');
        $prompt          = (string) $this->request->getVar('prompt');
        $modelId         = (string) $this->request->getVar('model_id');
        $uploadedFileIds = (array) $this->request->getVar('uploaded_media');

        // Validate that the requested model ID exists in the configuration
        $configs = MediaGenerationService::MEDIA_CONFIGS;
        if (! array_key_exists($modelId, $configs)) {
            return $this->_respondError('Invalid model ID selected.');
        }

        try {
            // Business logic and filesystem handling encapsulated strictly within MediaGenerationService
            $result = $this->mediaService->generateMediaFromPrompt($userId, $prompt, $uploadedFileIds, $modelId);

            // Handle Concurrency Conflict
            if (isset($result['status']) && $result['status'] === 'conflict') {
                return $this->_respondError($result['message'], 409);
            }

            // General Error from Service (e.g. Provider Error)
            if (isset($result['status']) && $result['status'] === 'error') {
                log_message('error', "[MediaController] Service error for User {$userId}: " . $result['message']);
                return $this->_respondError($result['message']);
            }

            // Set Flash Message
            if (($result['cost_deducted'] ?? 0) > 0) {
                session()->setFlashdata('success', "KSH " . number_format($result['cost_deducted'], 2) . " deducted.");
            }

            // Append CSRF token and Flash HTML to response for frontend refresh
            $result['csrf_token'] = csrf_hash();
            $result['flash_html'] = view('App\\Views\\partials\\flash_messages');

            // Security: Sanitize output URL
            if (isset($result['url'])) {
                try {
                    $result['url'] = $this->_sanitizeMediaUrl($result['url']);
                } catch (\RuntimeException $e) {
                    log_message('error', '[MediaController] URL sanitization failed: ' . $e->getMessage());
                    return $this->_respondError('Generated media contains invalid URL', 500);
                }
            }

            // Success Response - Handle Redirection for non-AJAX if needed, or consistent JSON
            if ($this->request->isAJAX()) {
                return $this->respond($result);
            }

            // Standard Post Back (e.g. form submission)
            return redirect()->back()->with('success', 'Media generated successfully.');
        } catch (\Exception $e) {
            log_message('error', '[MediaController] Exception: ' . $e->getMessage());
            return $this->_respondError('An unexpected error occurred during media generation.', 500);
        }
    }

    /**
     * Locates the currently active asynchronous job.
     *
     * Enables dashboard persistence and automatic session resumption.
     *
     * @return ResponseInterface JSON job object.
     */
    public function active(): ResponseInterface
    {
        $userId = (int) session()->get('userId');
        try {
            $job = $this->mediaService->getActiveJob($userId);

            return $this->respond([
                'status'     => 'success',
                'job'        => $job,
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[MediaController] Active job check error: ' . $e->getMessage());

            return $this->failServerError('Failed to fetch active job.');
        }
    }

    /**
     * Synchronizes status for long-running operations.
     *
     * @return ResponseInterface JSON status packet.
     */
    public function poll(): ResponseInterface
    {
        $opId = (string) $this->request->getVar('op_id');

        if ($opId === '') {
            return $this->fail('Operation ID is required.');
        }

        try {
            $result = $this->mediaService->pollVideoStatus($opId);

            // Security: Sanitize polling output
            if (isset($result['url'])) {
                try {
                    $result['url'] = $this->_sanitizeMediaUrl($result['url']);
                } catch (\RuntimeException $e) {
                    log_message('error', '[MediaController] Poll URL sanitization failed: ' . $e->getMessage());
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'Video URL validation failed',
                        'csrf_token' => csrf_hash()
                    ], 500);
                }
            }

            // Append CSRF token
            $result['csrf_token'] = csrf_hash();

            return $this->respond($result);
        } catch (\Exception $e) {
            log_message('error', '[MediaController] Poll Exception: ' . $e->getMessage());

            return $this->respond([
                'status' => 'error',
                'message' => 'Polling failed due to a server error.',
                'csrf_token' => csrf_hash()
            ], 500);
        }
    }

    /**
     * Delivers generated media resources.
     *
     * @param string $filename Resource identifier shard.
     *
     * @return ResponseInterface Streamed binary data.
     * @throws \CodeIgniter\Exceptions\PageNotFoundException
     */
    public function serve(string $filename): ResponseInterface
    {
        $userId = (int) session()->get('userId');
        $path   = WRITEPATH . 'uploads/generated/' . $userId . '/' . basename($filename);

        if (! file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getGet('download') === '1') {
            return $this->response->download($path, null);
        }

        $body = file_get_contents($path);
        if ($body === false) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Length', (string) filesize($path))
            ->setBody($body);
    }
}
