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
        Schema::table('sales_quatations', function (Blueprint $table) {
            $table->string('other_charge_name')->nullable()->after('quatation_type');
            $table->string('other_charge_amount')->nullable()->after('other_charge_name');
            $table->string('subsidy')->nullable()->after('other_charge_amount');
            $table->integer('bank_id')->nullable()->after('agent_sales_person_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_quatations', function (Blueprint $table) {
            //
        });
    }
};
