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
        Schema::table('account_listings', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_id',
                'pricing_model',
                'min_price',
                'auction_deadline',
                'sell_price',
                'buy_price',
                'is_verified',
                'reason',
                'step'
            ]);

            if (!Schema::hasColumn('account_listings', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->default(0)->after('user_id');
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
        Schema::table('account_listings', function (Blueprint $table) {
            $table->dropColumn('plan_id');

            $table->unsignedInteger('buyer_id')->default(0);
            $table->tinyInteger('pricing_model')->default(0);
            $table->decimal('min_price', 28, 8)->default(0);
            $table->date('auction_deadline')->nullable();
            $table->decimal('sell_price', 28, 8)->default(0);
            $table->decimal('buy_price', 28, 8)->default(0);
            $table->tinyInteger('is_verified')->default(0);
            $table->text('reason')->nullable();
            $table->tinyInteger('step')->default(0);
        });
    }
};
