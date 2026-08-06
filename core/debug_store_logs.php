<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\System\Communication\DispatchService;
use App\Enums\System\ChannelTypeEnum;
use Illuminate\Http\Request;
use App\Models\User;

try {
    $user = User::find(2);
    $service = app(DispatchService::class);

    $requestData = new Request([
        'contacts' => '923006859611',
        'message' => ['message_body' => 'DIRECT DEBUG DISPATCH TEST'],
        'schedule_at' => null,
        'method' => 'node',
        'gateway_id' => 8,
    ]);

    echo "CALLING storeDispatchLogs...\n";
    $result = $service->storeDispatchLogs(
        type: ChannelTypeEnum::WHATSAPP,
        request: $requestData,
        isCampaign: false,
        campaignId: null,
        user: $user,
        isApi: true,
        apiLogCount: 1,
    );

    echo "RESULT FROM storeDispatchLogs: " . json_encode($result) . "\n";
} catch (\Throwable $e) {
    echo "ERROR IN storeDispatchLogs: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
