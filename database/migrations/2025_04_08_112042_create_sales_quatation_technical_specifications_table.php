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
        Schema::create('sales_quatation_technical_specifications', function (Blueprint $table) {
            $table->id();
            $table->integer('sales_quatation_id');
            $table->text('itemDescription')->nullable();
            $table->string('qty')->nullable();
            $table->string('size')->nullable();
            $table->string('make')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('sales_quatation_technical_specifications');
    }
};
