<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$g1 = App\Models\Gateway::active()->where("id", 5)->select(["bulk_contact_limit", "type"])->first();
echo "WITHOUT ID IN SELECT: " . ($g1 ? "FOUND" : "NULL") . "\n";

$g2 = App\Models\Gateway::active()->where("id", 5)->select(["id", "bulk_contact_limit", "type"])->first();
echo "WITH ID IN SELECT: " . ($g2 ? "FOUND" : "NULL") . "\n";
