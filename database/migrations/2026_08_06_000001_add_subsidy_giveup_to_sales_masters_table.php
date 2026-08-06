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
        Schema::table('sales_masters', function (Blueprint $table) {
            $table->boolean('subsidy_giveup')->default(0)->after('subsidy_receveid')->comment('0 = No, 1 = Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_masters', function (Blueprint $table) {
            $table->dropColumn('subsidy_giveup');
        });
    }
};
