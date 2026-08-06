<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(2);
$req = new \Illuminate\Http\Request([
    'contacts' => '923006859611',
    'message' => ['message_body' => 'Hello from Panel! Test message to 923006859611.'],
    'schedule_at' => null,
    'method' => 'node',
    'gateway_id' => 5
]);

$dispatchService = new App\Services\System\Communication\DispatchService();
try {
    $res = $dispatchService->storeDispatchLogs(
        type: App\Enums\System\ChannelTypeEnum::WHATSAPP,
        request: $req,
        isCampaign: false,
        campaignId: null,
        user: $user,
        isApi: true,
        apiLogCount: 1
    );
    echo "SUCCESS: " . json_encode($res) . "\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
