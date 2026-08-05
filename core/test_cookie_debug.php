<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$account = App\Models\AccountListing::where('title', 'like', '%FlowCreation-Ai%')->first();
echo "ID: " . $account->id . " | TITLE: " . $account->title . "\n";

$rawInfo = $account->account_info;
if (is_string($rawInfo)) $rawInfo = json_decode($rawInfo, true);

$parts = [];
if (is_array($rawInfo)) {
    foreach ($rawInfo as $item) {
        $item = (array)$item;
        if (isset($item['name'], $item['value'])) {
            $parts[] = $item['name'] . '=' . $item['value'];
        }
    }
}
$cookieHeader = implode('; ', $parts);

$urls = [
    'https://labs.google/fx/api/auth/session',
    'https://labs.google/fx/api/trpc/project.listProjects?batch=1&input=%7B%7D',
    'https://labs.google/fx/api/trpc/user.getUserProfile?batch=1&input=%7B%7D',
    'https://labs.google/fx/api/trpc/flow.getUserQuota?batch=1&input=%7B%7D',
    'https://labs.google/fx/api/trpc/project.getProjects?batch=1&input=%7B%7D',
    'https://labs.google/fx/api/trpc/user.getProjectList?batch=1&input=%7B%7D'
];

foreach ($urls as $url) {
    echo "\nTesting: $url\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Cookie: ' . $cookieHeader,
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept: application/json, text/html, */*'
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP $code | RESP: " . substr(trim($resp), 0, 300) . "\n";
}
