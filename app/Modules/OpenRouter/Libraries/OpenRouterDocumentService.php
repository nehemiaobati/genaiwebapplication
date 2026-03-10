<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;
use Parsedown;

/**
 * OpenRouterDocumentService
 *
 * Self-contained document generation for the OpenRouter module.
 *
 * Strategy A (preferred): Pandoc — produces high-fidelity PDF and DOCX.
 * Strategy B (fallback):
 *   - PDF  → Dompdf
 *   - DOCX → PHPWord
 *
 * No cross-module dependencies.
 */
class OpenRouterDocumentService
{
    public function __construct(
        protected ?OpenRouterPandocService $pandocService = null
    ) {
        $this->pandocService = $pandocService ?? new OpenRouterPandocService();
    }

    // -------------------------------------------------------------------------
    // Private: PDF via Dompdf
    // -------------------------------------------------------------------------

    private function _generateWithDompdf(string $htmlContent, array $metadata): array
    {
        try {
            $options = new Options();
            $options->set('defaultFont', 'Calibri');
            $options->set('isRemoteEnabled', true);
            $options->set('isFontSubsettingEnabled', false);
            $options->set('defaultPaperSize', 'letter');
            $options->set('defaultPaperOrientation', 'portrait');
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', false);

            $userId  = session()->get('userId') ?? 0;
            $tempDir = WRITEPATH . 'uploads/dompdf_temp/openrouter_' . $userId;
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }
            $options->set('tempDir', $tempDir);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlContent, 'UTF-8');
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();
            $dompdf->addInfo('Title',    $metadata['title']);
            $dompdf->addInfo('Author',   $metadata['author']);
            $dompdf->addInfo('Subject',  $metadata['subject']);
            $dompdf->addInfo('Keywords', $metadata['keywords']);
            $dompdf->addInfo('Creator',  $metadata['creator']);

            return ['status' => 'success', 'fileData' => $dompdf->output()];
        } catch (\Throwable $e) {
            log_message('error', '[OpenRouterDocumentService] Dompdf: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'PDF generation failed.'];
        }
    }

    // -------------------------------------------------------------------------
    // Private: DOCX via PHPWord
    // -------------------------------------------------------------------------

    private function _generateWithPHPWord(string $markdownContent, array $metadata): array
    {
        try {
            // Fix: un-indent tables to avoid PHPWord table-in-list crash.
            $markdownContent = preg_replace('/^[\t ]+\|/m', '|', $markdownContent);
            $markdownContent = preg_replace('/^(?!\|)(.*)\n\|/m', "$1\n\n|", $markdownContent);

            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
            $parsedown->setBreaksEnabled(true);
            $htmlContent = $parsedown->text($markdownContent);

            $phpWord    = new \PhpOffice\PhpWord\PhpWord();
            $properties = $phpWord->getDocInfo();
            $properties->setCreator($metadata['creator']);
            $properties->setTitle($metadata['title']);
            $properties->setSubject($metadata['subject']);
            $properties->setKeywords($metadata['keywords']);
            $properties->setCompany($metadata['author']);

            $phpWord->setDefaultFontName('Calibri');
            $phpWord->setDefaultFontSize(11);

            $section = $phpWord->addSection([
                'marginLeft'   => 1440,
                'marginRight'  => 1440,
                'marginTop'    => 1440,
                'marginBottom' => 1440,
            ]);

            $footer = $section->addFooter();
            $footer->addPreserveText(
                'Page {PAGE} of {NUMPAGES}',
                ['alignment' => 'center', 'size' => 9, 'color' => '7f8c8d']
            );

            // Fix: escape ampersands before PHPWord XML serialisation.
            $fixedHtml = str_replace('&', '&amp;', $htmlContent);

            // Fix: preserve whitespace inside <pre><code> blocks.
            $fixedHtml = preg_replace_callback('/<pre><code(.*?)>(.*?)<\/code><\/pre>/s', function ($m) {
                $code = str_replace(["\r\n", "\r"], "\n", $m[2]);
                $code = str_replace("\n", '<br/>', $code);
                $code = str_replace(' ', '&nbsp;', $code);
                return '<pre><code' . $m[1] . ' style="font-family: \'Courier New\'; font-size: 9pt;">' . $code . '</code></pre>';
            }, $fixedHtml);

            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $fixedHtml, false, false);

            $tempFile  = tempnam(sys_get_temp_dir(), 'or_doc_');
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($tempFile);

            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \RuntimeException('Generated DOCX is empty.');
            }

            $fileData = file_get_contents($tempFile);
            if (!unlink($tempFile)) {
                log_message('error', '[OpenRouterDocumentService] Failed to delete temp DOCX: ' . $tempFile);
            }

            return ['status' => 'success', 'fileData' => $fileData];
        } catch (\Throwable $e) {
            log_message('error', '[OpenRouterDocumentService] PHPWord: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'DOCX generation failed: ' . $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Private: Styled HTML shell (shared by Pandoc input and Dompdf)
    // -------------------------------------------------------------------------

    private function _getStyledHtml(string $htmlContent, string $title = 'Document'): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{$safeTitle}</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; line-height: 1.6; color: #2c3e50; margin: 1in; }
        h1 { font-size: 22pt; font-weight: 700; color: #1a1a1a; border-bottom: 2px solid #3498db; padding-bottom: 8pt; margin: 24pt 0 12pt; }
        h2 { font-size: 16pt; font-weight: 600; color: #2c3e50; margin: 18pt 0 10pt; }
        h3 { font-size: 13pt; font-weight: 600; color: #34495e; margin: 14pt 0 8pt; }
        p  { margin: 0 0 10pt; }
        table { width: 100%; border-collapse: collapse; margin: 16pt 0; font-size: 10pt; }
        thead { background-color: #34495e; color: #fff; font-weight: 600; }
        th, td { border: 1px solid #bdc3c7; padding: 8pt 10pt; text-align: left; }
        tbody tr:nth-child(even) { background-color: #f8f9fa; }
        pre { background: #f4f4f4; border-left: 4px solid #3498db; padding: 12pt; margin: 12pt 0; font-family: "Courier New", monospace; font-size: 9pt; white-space: pre-wrap; }
        code { background: #ecf0f1; padding: 2pt 4pt; border-radius: 3px; font-family: "Courier New", monospace; font-size: 9pt; }
        pre code { background: none; padding: 0; }
        blockquote { border-left: 4px solid #95a5a6; margin: 12pt 0; padding: 8pt 12pt; background: #f8f9fa; font-style: italic; }
        ul, ol { margin: 10pt 0; padding-left: 30pt; }
        li { margin: 4pt 0; }
        a { color: #3498db; }
        hr { border: none; border-top: 1px solid #bdc3c7; margin: 16pt 0; }
    </style>
</head>
<body>{$htmlContent}</body>
</html>
HTML;
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Generates a document from Markdown content.
     *
     * Strategy A — Pandoc (preferred, when installed on the server).
     * Strategy B — Dompdf for PDF, PHPWord for DOCX as automatic fallbacks.
     *
     * @param string $markdownContent Raw Markdown AI response.
     * @param string $format          'pdf' or 'docx'.
     * @param array  $metadata        Optional: title, author, subject, keywords, creator.
     * @return array ['status' => 'success'|'error', 'fileData' => string|null, 'message' => string|null]
     */
    public function generate(string $markdownContent, string $format, array $metadata = []): array
    {
        $meta = array_merge([
            'title'    => 'OpenRouter AI Document',
            'author'   => 'AI Content Studio',
            'subject'  => 'Generated Content',
            'keywords' => 'AI, Content, Report',
            'creator'  => 'OpenRouter AI Studio',
        ], $metadata);

        // Build styled HTML for Pandoc input and Dompdf fallback
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $parsedown->setBreaksEnabled(true);
        $htmlContent = $parsedown->text($markdownContent);
        $styledHtml  = $this->_getStyledHtml($htmlContent, $meta['title']);

        // --- Strategy A: Pandoc ---
        if ($this->pandocService->isAvailable()) {
            $pandocResult = $this->pandocService->generate(
                $styledHtml,
                $format,
                'or_' . bin2hex(random_bytes(8))
            );

            if ($pandocResult['status'] === 'success' && file_exists($pandocResult['filePath'])) {
                $fileData = file_get_contents($pandocResult['filePath']);
                if (!unlink($pandocResult['filePath'])) {
                    log_message('error', '[OpenRouterDocumentService] Pandoc temp cleanup failed: ' . $pandocResult['filePath']);
                }
                return ['status' => 'success', 'fileData' => $fileData];
            }

            log_message('warning', '[OpenRouterDocumentService] Pandoc unavailable/failed, using fallback. Reason: ' . ($pandocResult['message'] ?? 'unknown'));
        }

        // --- Strategy B: Native fallbacks ---
        return match ($format) {
            'pdf'  => $this->_generateWithDompdf($styledHtml, $meta),
            'docx' => $this->_generateWithPHPWord($markdownContent, $meta),
            default => ['status' => 'error', 'message' => 'Unsupported format: ' . $format],
        };
    }
}
