<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$g = gs();
$g->admin_whatsapp = ["923006859611"];
$g->save();
echo "ADMIN WHATSAPP CONFIGURED: " . json_encode($g->admin_whatsapp) . "\n";
