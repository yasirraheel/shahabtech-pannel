<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$account = \App\Models\AccountListing::first();
echo "TESTING SYNCHRONOUS WHATSAPP ALERT TO ADMIN (923006859611)...\n";
\App\Lib\WhatsappNotification::sendCookieExpiryNotification($account, 'TEST DEBUG ALERT: COOKIE EXPIRED FOR LIVE TEST');
echo "DONE! CHECK LOGS BELOW:\n";

$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    echo shell_exec("tail -n 10 " . escapeshellarg($logPath));
}
