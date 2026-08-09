<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(2);
if ($user) {
    $user->api_whatsapp_gateway_id = 5;
    $user->save();
    echo "USER 2 REVERTED: api_whatsapp_gateway_id set back to 5\n";
}
