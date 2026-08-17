<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->json('rate_data')->nullable()->after('status_id');
        });
    }

    public function down()
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropColumn('rate_data');
        });
    }
};
