<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = \DB::getSchemaBuilder()->getColumnListing('contacts');
echo "CONTACTS TABLE COLUMNS: " . json_encode($cols) . "\n";

$contacts = \DB::table('contacts')->get();
echo "CONTACTS COUNT: " . $contacts->count() . "\n";
foreach ($contacts as $c) {
    echo json_encode($c) . "\n";
}
