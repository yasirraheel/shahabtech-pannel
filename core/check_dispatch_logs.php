<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logs = App\Models\DispatchLog::orderBy('id', 'desc')->take(10)->get();
echo "RECENT 10 DISPATCH LOGS IN OMNIREACH:\n";
foreach ($logs as $l) {
    $t = is_object($l->type) ? $l->type->value : $l->type;
    $s = is_object($l->status) ? $l->status->value : $l->status;
    echo "ID: {$l->id} | Type: {$t} | Status: {$s} | GatewayID: {$l->gatewayable_id} | CreatedAt: {$l->created_at}\n";
}
