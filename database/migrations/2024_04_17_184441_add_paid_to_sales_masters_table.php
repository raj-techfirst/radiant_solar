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
            $table->string('ragistration_portal')->nullable();
            $table->string('ragistration_number')->nullable();
            $table->string('feasibility_discom_sr_number')->nullable();
            $table->string('feasibility_amount')->nullable();
            $table->string('invoce_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('discom_sr_number')->nullable();
            $table->date('installation_date')->nullable();
            $table->string('make_of_solar_pv_module')->nullable();
            $table->string('model_no')->nullable();
            $table->string('type_of_PV_modules')->nullable();
            $table->string('capacity_of_folar_module')->nullable();
            $table->string('no_of_module')->nullable();
            $table->string('total_kw')->nullable();
            $table->string('make_of_inverter')->nullable();
            $table->string('model_no_id')->nullable();
            $table->string('type_of_inverter')->nullable();
            $table->string('voltage')->nullable();
            $table->string('no_of_inverter')->nullable();
            $table->string('structure_40_40_2mm')->nullable()->comment('Structure 40*40*2mm');
            $table->string('structure_60_40_2mm')->nullable()->comment('Structure 60*40*2mm');
            $table->string('structure_80_40_2mm')->nullable()->comment('Structure 80*40*2mm');
            $table->string('structure_others')->nullable();
            $table->string('dc')->nullable();
            $table->string('ac')->nullable();
            $table->string('la')->nullable();
            $table->string('earthing')->nullable();
            $table->string('d_c_side_earthing')->nullable();
            $table->string('a_c_side_earthing')->nullable();
            $table->string('l_a_earthing')->nullable();
            $table->string('phase_to_earth')->nullable();
            $table->string('phase_to_phase')->nullable();
            $table->string('photo_uplod')->nullable();
            $table->string('penal_photo')->nullable();
            $table->string('inveter_photo')->nullable();
            $table->string('couriar_ditails')->nullable();
            $table->string('couriar_no')->nullable();
            $table->string('meter_application_date')->nullable();
            $table->string('courair_company')->nullable();
            $table->string('reson')->nullable();
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
            //
        });
    }
};
