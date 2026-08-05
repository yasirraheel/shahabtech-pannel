<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$accounts = App\Models\AccountListing::where('status', 1)->get();
echo "TOTAL ACTIVE ACCOUNTS: " . count($accounts) . "\n\n";

foreach ($accounts as $account) {
    echo "========================================\n";
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
    echo "COOKIE COUNT: " . count($parts) . "\n";

    $urls = [
        'https://labs.google/fx/api/auth/session',
        'https://myaccount.google.com/'
    ];

    foreach ($urls as $url) {
        echo "URL: $url -> ";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Cookie: ' . $cookieHeader,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: application/json, text/html, */*'
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        echo "HTTP $code | EFFECTIVE: $eff | RESP: " . substr(trim($resp), 0, 150) . "\n";
    }
}
