<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Libraries;

/**
 * OpenRouterPandocService
 *
 * Self-contained Pandoc wrapper for the OpenRouter module.
 * Used by OpenRouterDocumentService as the preferred conversion strategy.
 */
class OpenRouterPandocService
{
    /**
     * Safely checks if pandoc is available on the system.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }

        try {
            $output = @shell_exec('command -v pandoc 2>/dev/null');
            return !empty($output);
        } catch (\Throwable $e) {
            log_message('info', '[OpenRouterPandocService] Shell unavailable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Converts HTML content to the requested format via Pandoc.
     *
     * @param string $htmlContent    Full styled HTML to convert.
     * @param string $outputFormat   'pdf' or 'docx'.
     * @param string $outputFilename Base filename (no extension).
     * @return array['status', 'filePath'|'message']
     */
    public function generate(string $htmlContent, string $outputFormat, string $outputFilename): array
    {
        $userId  = session()->get('userId') ?? 0;
        $tempDir = WRITEPATH . 'uploads/pandoc_temp/openrouter_' . $userId . '/';

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $ext            = ($outputFormat === 'pdf') ? 'pdf' : 'docx';
        $inputFilePath  = $tempDir . $outputFilename . '_in.html';
        $outputFilePath = $tempDir . $outputFilename . '.' . $ext;

        // Write HTML input file
        if (file_put_contents($inputFilePath, $htmlContent) === false) {
            return ['status' => 'error', 'message' => 'Write permission denied.'];
        }

        // Build and execute Pandoc command
        $cmd = sprintf(
            'pandoc --standalone %s -o %s',
            escapeshellarg($inputFilePath),
            escapeshellarg($outputFilePath)
        );
        exec($cmd . ' 2>&1', $cmdOutput, $returnCode);

        // Always clean up the input file
        if (file_exists($inputFilePath) && !unlink($inputFilePath)) {
            log_message('error', '[OpenRouterPandocService] Failed to delete input file: ' . $inputFilePath);
        }

        // Validate output
        if ($returnCode !== 0 || !file_exists($outputFilePath)) {
            log_message('error', '[OpenRouterPandocService] Failure: ' . implode("\n", $cmdOutput));
            if (file_exists($outputFilePath)) {
                unlink($outputFilePath);
            }
            return ['status' => 'error', 'message' => 'Pandoc conversion failed.'];
        }

        // Success: caller reads & deletes the file
        return [
            'status'   => 'success',
            'filePath' => $outputFilePath,
        ];
    }
}
