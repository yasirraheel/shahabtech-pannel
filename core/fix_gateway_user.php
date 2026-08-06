<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Assign Gateway 5 and Gateway 8 to User 2 (or allow all gateways)
\DB::table('gateways')->whereIn('id', [5, 8])->update(['user_id' => 2]);
echo "GATEWAYS 5 & 8 ASSIGNED TO USER 2!\n";
