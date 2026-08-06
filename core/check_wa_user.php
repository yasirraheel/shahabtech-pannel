<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = "e637cd7e-c2bb-406f-ad30-8ae69178e1f6";
$user = App\Models\User::where("api_key", $apiKey)->first();
$admin = App\Models\Admin::where("api_key", $apiKey)->first();

echo "USER: " . ($user ? $user->username . " (ID: {$user->id}, GatewayID: {$user->api_whatsapp_gateway_id})" : "NOT FOUND") . "\n";
echo "ADMIN: " . ($admin ? $admin->username . " (ID: {$admin->id})" : "NOT FOUND") . "\n";

// Check WhatsApp Gateways / Sessions in omnireach database
$gateways = App\Models\Gateways::where('channel', 'whatsapp')->get();
foreach ($gateways as $g) {
    echo "GATEWAY ID: {$g->id} | Name: {$g->name} | Type: {$g->type} | Status: {$g->status} | UserID: {$g->user_id}\n";
}

// Check WhatsApp Node Gateway / Sessions if model exists
$whatsappGateways = \DB::table('whatsapp_gateways')->get();
echo "\n--- WHATSAPP GATEWAYS ---\n";
foreach ($whatsappGateways as $wg) {
    echo json_encode($wg) . "\n";
}
