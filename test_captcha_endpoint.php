<?php
// Start fresh session
session_start();

// Load Laravel
require __DIR__ . '/bootstrap/app.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Create test controller and generate captcha
try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

    $request = \Illuminate\Http\Request::create('/captcha', 'GET');
    $response = $kernel->handle($request);

    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "Content Length: " . strlen($response->getContent()) . " bytes\n";

    // Check if it's valid PNG
    $content = $response->getContent();
    if (substr($content, 0, 8) === "\x89PNG\r\n\x1a\n") {
        echo "✅ Valid PNG image returned!\n";
    } else {
        echo "❌ Invalid content (not PNG format)\n";
        echo "First 100 chars: " . substr($content, 0, 100) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
