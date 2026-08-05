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
            $table->string('installation_asian_person')->nullable()->after('installation_date');
            $table->string('meter_asian_person')->nullable()->after('couriar_ditails');

            $table->string('total_amount')->nullable();
            $table->string('pending_amonut')->nullable()->after('total_amount');

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
