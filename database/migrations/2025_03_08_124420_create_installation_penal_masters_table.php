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
        Schema::create('installation_penal_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_master_id')->constrained()->onDelete('cascade');
            $table->foreignId('installation_id')->constrained()->onDelete('cascade');
            $table->string('stock_type')->nullable();
            $table->foreignId('item_group_id')->constrained()->onDelete('cascade');           
            $table->string('use_stock')->nullable();
            $table->string('model_number')->nullable();
            $table->string('total_kw')->nullable();
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
        Schema::dropIfExists('installation_penal_masters');
    }
};
