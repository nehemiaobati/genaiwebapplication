<?php
// test_email_config.php
require_once __DIR__.'/vendor/autoload.php';
use Config\Services;

$config = config('Email');
echo "SMTPHost: " . $config->SMTPHost . PHP_EOL;
echo "fromEmail: " . $config->fromEmail . PHP_EOL;
echo "SMTPPort: " . $config->SMTPPort . PHP_EOL;
echo "SMTPUser: " . $config->SMTPUser . PHP_EOL;
