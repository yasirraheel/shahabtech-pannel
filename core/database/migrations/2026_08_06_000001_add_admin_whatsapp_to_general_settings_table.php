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
        if (!Schema::hasColumn('general_settings', 'admin_whatsapp')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->text('admin_whatsapp')->nullable()->after('banner_color');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('general_settings', 'admin_whatsapp')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn('admin_whatsapp');
            });
        }
    }
};
