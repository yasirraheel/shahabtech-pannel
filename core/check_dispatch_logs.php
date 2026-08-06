<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logs = App\Models\DispatchLog::orderBy('id', 'desc')->take(10)->get();
echo "RECENT 10 DISPATCH LOGS IN OMNIREACH:\n";
foreach ($logs as $l) {
    echo "ID: {$l->id} | Type: {$l->type} | Status: " . (is_object($l->status) ? $l->status->value : $l->status) . " | GatewayID: {$l->gatewayable_id} | CreatedAt: {$l->created_at}\n";
}
