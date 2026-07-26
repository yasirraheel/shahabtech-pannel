<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'is_trial')) {
        $table->boolean('is_trial')->default(0)->after('expires_at');
    }
    if (!Schema::hasColumn('users', 'pending_trial_minutes')) {
        $table->integer('pending_trial_minutes')->nullable()->after('is_trial');
    }
});
echo "Done\n";
