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
            $table->string('proforma_15')->nullable()->comment('Meter installation proforma 15 (doc)');
            $table->date('meter_installation_date')->nullable();
            $table->date('subsidy_disbursement_date')->nullable();
            $table->date('subsidy_disbursement_verify_date')->nullable();
            $table->string('subsidy_disbursal_remark')->nullable();
            $table->date('subsidy_request_date')->nullable();
            $table->string('meter_application_oc')->nullable()->comment('Meter Application OC Copy (doc)');
            $table->string('payment_receipt')->nullable()->comment('Payment Receipt (doc)');
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
