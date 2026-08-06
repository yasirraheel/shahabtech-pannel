<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update User 2
$user = App\Models\User::find(2);
if ($user) {
    $user->api_key = "e637cd7e-c2bb-406f-ad30-8ae69178e1f6";
    $user->api_whatsapp_gateway_id = 5;
    $user->save();
    echo "USER 2 UPDATED SUCCESSFULLY! API Key: {$user->api_key}\n";
} else {
    echo "USER 2 NOT FOUND\n";
}
