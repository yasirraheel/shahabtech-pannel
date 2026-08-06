<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$account = \App\Models\AccountListing::with('socialMedia')->first();
if ($account) {
    echo "SENDING ACTUAL FORMATTED COOKIE EXPIRY ALERT FOR ACCOUNT: {$account->title}\n";
    \App\Lib\WhatsappNotification::sendCookieExpiryNotification($account, 'Session expired (Unauthenticated on Google Flow API)');
    echo "SENT!\n";
} else {
    echo "NO ACCOUNT FOUND!\n";
}
