<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check subscriptions table
$subs = \DB::table('subscriptions')->where('user_id', 2)->get();
echo "SUBSCRIPTIONS FOR USER 2:\n";
foreach ($subs as $s) {
    echo json_encode($s) . "\n";
}

// Extend or insert active subscription
\DB::table('subscriptions')->where('user_id', 2)->update([
    'status' => 1,
    'expired_at' => '2030-12-31 23:59:59',
]);
echo "SUBSCRIPTION UPDATED TO ACTIVE (2030)!\n";
