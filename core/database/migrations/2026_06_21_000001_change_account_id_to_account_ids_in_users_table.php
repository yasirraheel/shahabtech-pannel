<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop old column if exists
            if (Schema::hasColumn('users', 'account_id')) {
                $table->dropColumn('account_id');
            }
            
            // Add new JSON column
            if (!Schema::hasColumn('users', 'account_ids')) {
                $table->json('account_ids')->nullable()->after('plan_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'account_ids')) {
                $table->dropColumn('account_ids');
            }
            
            if (!Schema::hasColumn('users', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('plan_id');
            }
        });
    }
};
