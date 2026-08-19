<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rate_given_table', function (Blueprint $table) {
            $table->integer('created_by')->nullable()->after('is_hide');
        });
    }

    public function down()
    {
        Schema::table('rate_given_table', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
