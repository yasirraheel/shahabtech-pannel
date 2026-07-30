<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'min_extension_version')) {
                $table->string('min_extension_version', 50)->default('1.9.6')->nullable();
            }
            if (!Schema::hasColumn('general_settings', 'force_extension_update')) {
                $table->tinyInteger('force_extension_update')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'min_extension_version')) {
                $table->dropColumn('min_extension_version');
            }
            if (Schema::hasColumn('general_settings', 'force_extension_update')) {
                $table->dropColumn('force_extension_update');
            }
        });
    }
};
