<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\DB::table('general_settings')->update([
    'min_extension_version' => '2.0.0',
    'force_extension_update' => 1
]);

echo "SUCCESS: min_extension_version updated to 2.0.0\n";
