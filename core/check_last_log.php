<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$log = App\Models\DispatchLog::orderBy('id', 'desc')->first();
echo "LAST DISPATCH LOG IN OMNIREACH:\n";
echo "ID: {$log->id}\n";
echo "Type: " . (is_object($log->type) ? $log->type->value : $log->type) . "\n";
echo "Status: " . (is_object($log->status) ? $log->status->value : $log->status) . "\n";
echo "GatewayID: {$log->gatewayable_id}\n";
echo "ResponseMsg: " . json_encode($log->response_message) . "\n";
echo "CreatedAt: {$log->created_at}\n";
