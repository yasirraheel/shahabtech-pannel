<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check Status::ACTIVE->value
echo "STATUS ACTIVE VALUE: " . App\Enums\Status::ACTIVE->value . "\n";

// Update status = 1 for active gateways
\DB::table('gateways')->whereIn('id', [5, 8])->update(['status' => App\Enums\Status::ACTIVE->value]);
echo "GATEWAY STATUSES UPDATED TO ACTIVE VALUE!\n";
