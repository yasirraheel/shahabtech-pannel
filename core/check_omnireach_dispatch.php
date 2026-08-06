<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$log = \App\Models\DispatchLog::orderBy('id', 'desc')->first();
echo "NEW LAST DISPATCH LOG IN OMNIREACH:\n";
echo "ID: " . ($log ? $log->id : 'NONE') . "\n";
if ($log) {
    $t = is_object($log->type) ? $log->type->value : $log->type;
    $s = is_object($log->status) ? $log->status->value : $log->status;
    echo "Type: {$t} | Status: {$s} | GatewayID: {$log->gatewayable_id} | CreatedAt: {$log->created_at}\n";
    echo "ResponseMsg: " . json_encode($log->response_message) . "\n";
}
