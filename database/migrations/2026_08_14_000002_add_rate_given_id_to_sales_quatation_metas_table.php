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
        Schema::table('sales_quatation_metas', function (Blueprint $table) {
            $table->integer('rate_given_id')->nullable()->after('sales_quatation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_quatation_metas', function (Blueprint $table) {
            $table->dropColumn('rate_given_id');
        });
    }
};