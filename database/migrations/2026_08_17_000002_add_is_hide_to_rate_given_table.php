<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rate_given_table', function (Blueprint $table) {
            $table->tinyInteger('is_hide')->default(0)->after('total_taxable');
        });
    }

    public function down()
    {
        Schema::table('rate_given_table', function (Blueprint $table) {
            $table->dropColumn('is_hide');
        });
    }
};
