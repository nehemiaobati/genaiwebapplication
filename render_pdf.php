<?php
/**
 * Markdown to PDF Conversion Script using Dompdf.
 *
 * This script reads the 'documentation.md' file, converts it to HTML using Parsedown,
 * and then generates a PDF using the Dompdf library. It is designed to be run from
 * the command line in the project's root directory.
 *
 * Requirements:
 * - Composer dependencies must be installed (`composer install`).
 * - The script must be run via the PHP CLI.
 *
 * Usage:
 * php render_pdf.php
 */

declare(strict_types=1);

// --- Step 1: Bootstrap the Application Environment ---
// This is the most crucial step. It includes Composer's autoloader, making
// all your installed libraries (like Parsedown and Dompdf) available.
echo "--- Initializing ---\n";
$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    echo "❌ ERROR: Composer autoloader not found.\n";
    echo "Please run 'composer install' before using this script.\n";
    exit(1);
}
require $autoloader;

// Import the necessary classes.
use Parsedown;
use Dompdf\Dompdf;
use Dompdf\Options;

// --- Step 2: Define Configuration and Paths ---
echo "--- Configuring paths ---\n";
define('INPUT_FILE', __DIR__ . '/documentation.md');
define('OUTPUT_DIR', __DIR__ . '/public/assets');
define('OUTPUT_FILE', OUTPUT_DIR . '/Web Platform.pdf');

// --- Main Execution Block ---
try {
    // --- Step 3: Pre-flight Checks ---
    echo "--- Running pre-flight checks ---\n";

    // Check if the input file exists and is readable.
    if (!is_readable(INPUT_FILE)) {
        throw new Exception("Input file not found or is not readable: " . INPUT_FILE);
    }

    // Check if the output directory exists. If not, create it.
    if (!is_dir(OUTPUT_DIR)) {
        echo "Output directory not found. Creating it...\n";
        if (!mkdir(OUTPUT_DIR, 0775, true)) {
            throw new Exception("Failed to create output directory: " . OUTPUT_DIR);
        }
    }
    echo "Checks passed.\n\n";

    // --- Step 4: Read and Convert Markdown to HTML ---
    echo "--- Step 1/3: Reading and parsing Markdown file... ---\n";
    $markdownContent = file_get_contents(INPUT_FILE);
    if ($markdownContent === false) {
        throw new Exception("Failed to read content from " . INPUT_FILE);
    }

    $parsedown = new Parsedown();
    $htmlContent = $parsedown->text($markdownContent);
    echo "Markdown converted to HTML successfully.\n\n";

    // --- Step 5: Generate PDF from HTML using Dompdf ---
    echo "--- Step 2/3: Configuring Dompdf... ---\n";

    // It's good practice to wrap the HTML in a basic structure with a UTF-8 meta tag
    // and basic styling for better rendering, especially for complex documents.
    $fullHtml = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body { font-family: DejaVu Sans, sans-serif; line-height: 1.6; font-size: 12px; }
            pre { background-color: #f4f4f4; padding: 10px; border: 1px solid #ddd; white-space: pre-wrap; word-wrap: break-word; }
            code { font-family: DejaVu Sans Mono, monospace; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            th { background-color: #f2f2f2; }
            hr { border: 0; border-top: 1px solid #ccc; }
        </style>
    </head>
    <body>' . $htmlContent . '</body>
    </html>';

    $options = new Options();
    // Enable remote asset loading (if your markdown has external images)
    $options->set('isRemoteEnabled', true);
    // Set a default font that supports a wide range of characters, including symbols
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($fullHtml);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    echo "--- Step 3/3: Rendering PDF... (This may take a moment) ---\n";
    // Render the HTML as PDF
    $dompdf->render();

    // Get the generated PDF output
    $pdfOutput = $dompdf->output();

    // --- Step 6: Save the PDF to a File ---
    if (file_put_contents(OUTPUT_FILE, $pdfOutput) === false) {
        throw new Exception("Failed to write PDF to file: " . OUTPUT_FILE);
    }

    echo "\n-------------------------------------------------\n";
    echo "✅ Success! Documentation converted successfully.\n";
    echo "PDF saved to: " . OUTPUT_FILE . "\n";
    echo "-------------------------------------------------\n\n";

} catch (Throwable $e) {
    // Catch any error or exception that occurs during the process
    echo "\n-------------------------------------------------\n";
    echo "❌ ERROR: An error occurred during conversion.\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "-------------------------------------------------\n\n";
    exit(1);
}

exit(0);