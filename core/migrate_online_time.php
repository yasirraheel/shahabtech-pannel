<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'total_online_time')) {
        $table->bigInteger('total_online_time')->default(0)->after('last_seen_ip');
    }
});
echo "Done\n";
