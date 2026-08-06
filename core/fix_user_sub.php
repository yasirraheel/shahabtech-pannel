<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\DB::table('subscriptions')->where('id', 5)->update([
    'status' => 1,
    'expired_date' => '2030-12-31 23:59:59',
]);
echo "SUBSCRIPTION 5 UPDATED TO ACTIVE (2030)!\n";
