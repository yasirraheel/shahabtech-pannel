<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(2);
$user->api_whatsapp_gateway_id = 8;
$user->save();
echo "UPDATED GATEWAY TO 8 (Mohsin) FOR USER 2\n";
