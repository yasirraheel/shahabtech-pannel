<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!Illuminate\Support\Facades\Schema::hasColumn('users', 'is_tester')) {
    Illuminate\Support\Facades\Schema::table('users', function ($table) {
        $table->tinyInteger('is_tester')->default(0)->after('status');
    });
    echo "Column is_tester added successfully!\n";
} else {
    echo "Column is_tester already exists!\n";
}
