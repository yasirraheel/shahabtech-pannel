<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Jobs\ProcessDispatchLogBatch;
use App\Enums\System\ChannelTypeEnum;

try {
    echo "CALLING ProcessDispatchLogBatch::dispatchSync FOR LOG 294...\n";
    ProcessDispatchLogBatch::dispatchSync([294], ChannelTypeEnum::WHATSAPP, 'regular', false);
    echo "SUCCESSFULLY DISPATCHED LOG 294!\n";

    $log = \App\Models\DispatchLog::find(294);
    echo "STATUS FOR LOG 294: " . (is_object($log->status) ? $log->status->value : $log->status) . "\n";
    echo "RESPONSE MSG: " . json_encode($log->response_message) . "\n";
} catch (\Throwable $e) {
    echo "ERROR IN DISPATCH SYNC: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
