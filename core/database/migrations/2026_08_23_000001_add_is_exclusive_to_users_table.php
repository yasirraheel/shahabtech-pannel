<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_exclusive')) {
            Schema::table('users', function (Blueprint $table) {
                $table->tinyInteger('is_exclusive')->default(0)->after('is_tester');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_exclusive')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_exclusive');
            });
        }
    }
};
