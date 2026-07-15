<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pageId = config('facebook.page_id');
$accessToken = config('facebook.access_token');

echo "Page ID: {$pageId}\n";
echo "Token length: " . strlen($accessToken) . "\n";
echo "Token prefix: " . substr($accessToken, 0, 30) . "\n\n";

// Test 1: Simple token introspection
echo "--- Test 1: Debug token ---\n";
$debugUrl = "https://graph.facebook.com/v21.0/debug_token";
$debugResponse = \Illuminate\Support\Facades\Http::get($debugUrl, [
    'input_token' => $accessToken,
    'access_token' => $accessToken,
]);
echo json_encode($debugResponse->json(), JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Get page photos
echo "--- Test 2: Get page photos ---\n";
$feedUrl = "https://graph.facebook.com/v21.0/{$pageId}/photos";
$feedResponse = \Illuminate\Support\Facades\Http::get($feedUrl, [
    'fields' => 'id,name,created_time,link,source,type',
    'access_token' => $accessToken,
    'type' => 'uploaded',
    'limit' => 5,
]);
echo json_encode($feedResponse->json(), JSON_PRETTY_PRINT) . "\n";
