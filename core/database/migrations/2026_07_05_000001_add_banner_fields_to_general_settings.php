<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBannerFieldsToGeneralSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('banner_status')->default(0);
            $table->text('banner_message')->nullable();
            $table->string('banner_cta_text')->nullable();
            $table->string('banner_cta_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['banner_status', 'banner_message', 'banner_cta_text', 'banner_cta_link']);
        });
    }
}
