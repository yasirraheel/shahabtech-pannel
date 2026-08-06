<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logs = \App\Models\DispatchLog::orderBy('id', 'desc')->take(5)->get();
echo "LAST 5 DISPATCH LOGS IN OMNIREACH:\n";
foreach ($logs as $log) {
    $t = is_object($log->type) ? $log->type->value : $log->type;
    $s = is_object($log->status) ? $log->status->value : $log->status;
    echo "ID: {$log->id} | Type: {$t} | Status: {$s} | GatewayID: {$log->gatewayable_id} | CreatedAt: {$log->created_at} | MsgID: {$log->message_id} | ContactID: {$log->contact_id}\n";
    echo "ResponseMsg: " . json_encode($log->response_message) . "\n";
}
