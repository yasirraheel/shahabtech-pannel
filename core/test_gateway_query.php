<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(2);
$gw = App\Models\Gateway::where('channel', App\Enums\System\ChannelTypeEnum::WHATSAPP)
    ->where('type', 'node')
    ->where('uid', '3ZZ2UPim-YVwgoqdzD3h0Lr-KiIUPiEI')
    ->where('user_id', 2)
    ->first();

echo "GATEWAY QUERY RESULT: " . json_encode($gw) . "\n";

// Fix omnireach WhatsAppController lines if needed
$gwManager = new App\Managers\GatewayManager();
$res = $gwManager->getSpecificGateway(
    channel: App\Enums\System\ChannelTypeEnum::WHATSAPP,
    type: 'node',
    column: 'uid',
    value: '3ZZ2UPim-YVwgoqdzD3h0Lr-KiIUPiEI',
    user: $user
);
echo "GW MANAGER RESULT: " . json_encode($res) . "\n";
