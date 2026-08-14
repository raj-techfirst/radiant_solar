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
        Schema::create('rate_given_table', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_master_id')->nullable();
            $table->string('type')->nullable();
            $table->integer('item_id')->nullable();
            $table->integer('item_group_id')->nullable();
            $table->string('nos')->nullable();
            $table->string('rate')->nullable();
            $table->string('item_gst')->nullable();
            $table->string('total_taxable')->nullable();
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
        Schema::dropIfExists('rate_given_table');
    }
};