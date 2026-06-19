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
        Schema::table('social_media', function (Blueprint $table) {
            if (!Schema::hasColumn('social_media', 'domain')) {
                $table->string('domain')->nullable()->after('name');
            }
            if (!Schema::hasColumn('social_media', 'url')) {
                $table->string('url')->nullable()->after('domain');
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
        Schema::table('social_media', function (Blueprint $table) {
            $table->dropColumn(['domain', 'url']);
        });
    }
};
