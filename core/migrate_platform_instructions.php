<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('social_media', function (Blueprint $table) {
    if (!Schema::hasColumn('social_media', 'instructions')) {
        $table->text('instructions')->nullable()->after('url');
    }
});
echo "Done\n";
