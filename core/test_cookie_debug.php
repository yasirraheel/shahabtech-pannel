<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$accounts = App\Models\AccountListing::where('status', 1)->get();
echo "TESTING ALL ACTIVE ACCOUNTS WITH GOOGLE SESSION EXPIRY CHECK:\n\n";

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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://labs.google/fx/api/auth/session');
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
    curl_close($ch);

    $json = json_decode($resp, true);
    if (empty($json) || !isset($json['user']) || empty($json['user'])) {
        echo "RESULT: INVALID (No active session user object)\n";
    } else {
        $expStr = $json['expires'] ?? null;
        $expTs = $expStr ? strtotime($expStr) : null;
        if ($expTs && $expTs < time()) {
            echo "RESULT: INVALID (Google Session Expired at $expStr, Current: " . date('Y-m-d H:i:s') . " UTC)\n";
        } else {
            echo "RESULT: VALID (Active until $expStr)\n";
        }
    }
}
