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
        Schema::create('serial_number_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serial_number_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_challan_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_challan_meta_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('serial_number_logs');
    }
};
