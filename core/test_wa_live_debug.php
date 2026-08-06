<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$numbers = (array) gs('admin_whatsapp');
echo "CONFIGURED ADMIN WHATSAPP NUMBERS: " . json_encode($numbers) . "\n";

$account = \App\Models\AccountListing::first();
echo "TESTING WITH ACCOUNT: " . ($account ? $account->title : 'NONE') . "\n";

if ($account) {
    echo "CALLING sendCookieExpiryNotification...\n";
    \App\Lib\WhatsappNotification::sendCookieExpiryNotification($account, 'TEST DEBUG EXPIRED COOKIE ALERT');
}

// Read storage/logs/laravel.log to see output
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    echo "\nRECENT LARAVEL LOG:\n";
    echo shell_exec("tail -n 20 " . escapeshellarg($logPath));
}
