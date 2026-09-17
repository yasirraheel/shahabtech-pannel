<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('account_listings', 'last_synced_at')) {
            Schema::table('account_listings', function (Blueprint $table) {
                $table->timestamp('last_synced_at')->nullable()->after('cookie_check_error');
                $table->string('last_sync_source', 64)->nullable()->after('last_synced_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('account_listings', 'last_synced_at')) {
            Schema::table('account_listings', function (Blueprint $table) {
                $table->dropColumn(['last_synced_at', 'last_sync_source']);
            });
        }
    }
};
