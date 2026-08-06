<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(2);
$gwActive = App\Models\Gateway::active()->get();
echo "ACTIVE GATEWAYS COUNT: " . $gwActive->count() . "\n";
foreach ($gwActive as $g) {
    echo "ID: {$g->id} | Name: {$g->name} | Status: {$g->status}\n";
}
