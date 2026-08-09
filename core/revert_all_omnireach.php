<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Set user_id = NULL for all WhatsApp gateways (making them owned by Super Admin)
\DB::table('gateways')->whereIn('id', [5, 8])->update(['user_id' => null]);
echo "GATEWAYS 5 & 8 user_id SET TO NULL (Owned by Admin)\n";

// Print current gateways state
$gateways = \DB::table('gateways')->get();
foreach ($gateways as $g) {
    echo "ID: {$g->id} | UserID: " . json_encode($g->user_id) . " | Name: {$g->name} | Status: {$g->status}\n";
}
