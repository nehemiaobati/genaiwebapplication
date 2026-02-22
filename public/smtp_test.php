<?php

/**
 * Standalone SMTP Connectivity Test
 * 
 * Upload this to your public/ folder and access it via your browser.
 * e.g., https://yourdomain.com/smtp_test.php
 */

header('Content-Type: text/plain');

$hosts = [
    'smtp.gmail.com' => [587, 465, 25]
];

echo "--- SMTP Connectivity Test ---\n\n";

foreach ($hosts as $host => $ports) {
    foreach ($ports as $port) {
        echo "Testing $host:$port ... ";

        $errno = 0;
        $errstr = '';
        $timeout = 5;

        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($fp) {
            echo "SUCCESS\n";
            fclose($fp);
        } else {
            echo "FAILED ($errno: $errstr)\n";
        }
    }
    echo "\n";
}

echo "--- End of Test ---\n";
unlink(__FILE__); // Self-delete for security
echo "This script has been deleted for security. Refreshing will result in a 404.\n";
