<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$cols = Schema::getColumnListing('admins');
echo "ADMINS TABLE COLUMNS: " . json_encode($cols) . "\n";
