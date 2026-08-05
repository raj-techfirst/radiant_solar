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
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_master_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('penal_company_id')->default(0);
            $table->string('penal_model_no')->nullable();
            $table->integer('penal_type_id')->default(0);
            $table->integer('penal_watt_id')->default(0);
            $table->string('penal_nos')->nullable();
            $table->string('total_kv')->nullable();
            $table->string('type_of_inverter')->nullable();
            $table->string('voltage')->nullable();
            $table->string('no_of_inverter')->nullable();
            $table->string('structure')->nullable();
            $table->string('dc_side')->nullable();
            $table->string('ac_side')->nullable();
            $table->string('la_earthing')->nullable();
            $table->string('phase_to_earth')->nullable();
            $table->string('phase_to_phase')->nullable();
            $table->string('image')->nullable();
            $table->string('penal_image')->nullable();
            $table->string('inverter_image')->nullable();
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
        Schema::dropIfExists('installations');
    }
};
