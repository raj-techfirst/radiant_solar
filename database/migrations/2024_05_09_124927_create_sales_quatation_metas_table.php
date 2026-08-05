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
        Schema::create('sales_quatation_metas', function (Blueprint $table) {
            $table->id();
            $table->integer('sales_quatation_id')->nullable();
            $table->integer('item_id')->nullable();
            $table->string('nos')->nullable();
            $table->string('rate')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_quatation_metas');
    }
};
