<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$gateways = \DB::table('gateways')->get();
echo "ALL GATEWAYS IN OMNIREACH:\n";
foreach ($gateways as $g) {
    echo "ID: {$g->id} | UserID: " . json_encode($g->user_id) . " | Name: {$g->name} | Type: {$g->type} | Channel: {$g->channel} | Status: {$g->status}\n";
}
