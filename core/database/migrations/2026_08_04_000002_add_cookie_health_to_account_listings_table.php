<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('account_listings', 'cookie_status')) {
            Schema::table('account_listings', function (Blueprint $table) {
                $table->tinyInteger('cookie_status')->default(2)->after('status')->comment('1: Valid, 0: Invalid, 2: Unchecked');
                $table->timestamp('cookie_checked_at')->nullable()->after('cookie_status');
                $table->text('cookie_check_error')->nullable()->after('cookie_checked_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('account_listings', 'cookie_status')) {
            Schema::table('account_listings', function (Blueprint $table) {
                $table->dropColumn(['cookie_status', 'cookie_checked_at', 'cookie_check_error']);
            });
        }
    }
};
