<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "STATUS ACTIVE VALUE: " . App\Enums\Common\Status::ACTIVE->value . "\n";

\DB::table('gateways')->whereIn('id', [5, 8])->update(['status' => App\Enums\Common\Status::ACTIVE->value]);
echo "GATEWAY STATUSES UPDATED TO '1'!\n";
