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
        Schema::create('sales_quatations', function (Blueprint $table) {
            $table->id();
            $table->string('mobile')->nullable();
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->string('ship_to')->nullable();
            $table->string('gst_no')->nullable();
            $table->integer('item_id')->nullable();
            $table->string('nos')->nullable();
            $table->string('rate')->nullable();
            $table->string('reference')->nullable();
            $table->string('agent_sales_person')->nullable();
            $table->integer('penal_company_id')->nullable();
            $table->integer('penal_type_id')->nullable();
            $table->integer('penal_watt_id')->nullable();
            $table->string('penal_nos')->nullable();
            $table->string('pv_capacity_kw')->nullable();
            $table->string('inveter_capacity')->nullable();
            $table->string('no_of_inveter')->nullable();
            $table->string('structure')->nullable();
            $table->string('common_meter')->nullable();
            $table->string('total_system_cost')->nullable();
            $table->string('meter_charges')->nullable();
            $table->string('registration_fee')->nullable();
            $table->string('rate_per_kw')->nullable();
            $table->string('gst')->nullable();
            $table->string('quatation_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_quatations');
    }
};
