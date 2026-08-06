<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$account = \App\Models\AccountListing::first();
echo "TESTING SYNCHRONOUS WHATSAPP ALERT TO ADMIN (923006859611)...\n";
\App\Lib\WhatsappNotification::sendCookieExpiryNotification($account, 'TEST DEBUG ALERT: COOKIE EXPIRED FOR LIVE TEST');
echo "DONE! CHECKING OMNIREACH DISPATCH LOG:\n";

$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
$log = \DB::table('u559276167_omnireach.dispatch_logs')->orderBy('id', 'desc')->first();
echo json_encode($log) . "\n";
