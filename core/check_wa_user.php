<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = "e637cd7e-c2bb-406f-ad30-8ae69178e1f6";
$admin = App\Models\Admin::where("api_key", $apiKey)->first();

echo "ADMIN: " . ($admin ? $admin->username . " (ID: {$admin->id})" : "NOT FOUND") . "\n";
echo "SITE SETTING api_whatsapp_gateway_id: " . site_settings('api_whatsapp_gateway_id') . "\n";

echo "\n--- GATEWAYS TABLE ---\n";
$gateways = \DB::table('gateways')->get();
foreach ($gateways as $g) {
    echo json_encode($g) . "\n";
}

echo "\n--- WHATSAPP SESSIONS / DEVICE TABLE ---\n";
$tables = \DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $tName = array_values((array)$t)[0];
    if (str_contains($tName, 'whatsapp') || str_contains($tName, 'gateway') || str_contains($tName, 'device') || str_contains($tName, 'session')) {
        echo "TABLE: $tName\n";
        $rows = \DB::table($tName)->get();
        foreach ($rows as $r) {
            echo "  " . json_encode($r) . "\n";
        }
    }
}
