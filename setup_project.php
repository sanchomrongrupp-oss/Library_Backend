<?php
/**
 * Setup script to download composer.phar and install project dependencies
 */

echo "=== Setting up Library Backend ===\n";

$composerPhar = __DIR__ . '/composer.phar';

if (!file_exists($composerPhar)) {
    echo "1. Downloading composer.phar ...\n";
    $url = 'https://getcomposer.org/composer.phar';
    
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: PHP\r\n",
            'timeout' => 300,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];
    $context = stream_context_create($options);
    $data = @file_get_contents($url, false, $context);
    
    if ($data === false) {
        // Try curl fallback
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            $data = curl_exec($ch);
            curl_close($ch);
        }
    }
    
    if ($data !== false && strlen($data) > 100000) {
        file_put_contents($composerPhar, $data);
        echo "composer.phar downloaded successfully (" . round(strlen($data) / 1024 / 1024, 2) . " MB).\n";
    } else {
        echo "Failed to download composer.phar automatically.\n";
        exit(1);
    }
} else {
    echo "1. composer.phar already exists.\n";
}

echo "2. Installing dependencies via composer.phar ...\n";
passthru('php composer.phar install --prefer-dist --no-interaction', $returnCode);

if ($returnCode !== 0) {
    echo "Composer install returned exit code $returnCode\n";
    exit($returnCode);
}

echo "3. Dependencies installed successfully!\n";
