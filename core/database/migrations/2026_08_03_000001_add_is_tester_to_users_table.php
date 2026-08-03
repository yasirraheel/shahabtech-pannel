<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_tester')) {
            Schema::table('users', function (Blueprint $table) {
                $table->tinyInteger('is_tester')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_tester')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_tester');
            });
        }
    }
};
