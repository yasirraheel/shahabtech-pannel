<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warzone_purchased_links', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->default('Gemini AI Pro 18M');
            $table->string('service_id')->nullable();
            $table->string('order_id')->nullable();
            $table->text('link');
            $table->string('source')->default('bot'); // 'bot' or 'manual'
            $table->tinyInteger('status')->default(1); // 1 = Available, 2 = Active, 3 = Used, 0 = Expired
            $table->string('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warzone_purchased_links');
    }
};
