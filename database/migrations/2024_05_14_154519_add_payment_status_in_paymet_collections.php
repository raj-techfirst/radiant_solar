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
        Schema::table('paymet_collections', function (Blueprint $table) {
            $table->boolean('status')->default(0)->after('upi_id');
            $table->integer('approved_by')->nullable()->after('status');
            $table->string('remarks')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paymet_collections', function (Blueprint $table) {
            //
        });
    }
};
