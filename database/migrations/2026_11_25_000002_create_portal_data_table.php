<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_master_id')->constrained()->cascadeOnDelete();
            $table->string('consumer_number')->nullable()->index();
            $table->string('current_status_of_application')->nullable();
            $table->string('vendor_consumer_agreement_uploaded')->nullable();
            $table->string('vendor_consumer_agreement_uploading_date')->nullable();
            $table->string('loan_status')->nullable();
            $table->string('loan_rejection_date')->nullable();
            $table->string('loan_disbursed_date_first_tranche')->nullable();
            $table->string('loan_disbursed_amount_first_tranche')->nullable();
            $table->string('loan_first_tranche_disbursal_utr')->nullable();
            $table->string('loan_disbursed_date_second_tranche')->nullable();
            $table->string('loan_disbursed_amount_second_tranche')->nullable();
            $table->string('loan_second_tranche_disbursal_utr')->nullable();
            $table->string('feasibility_applied')->nullable();
            $table->string('installed_pv_module_capacity')->nullable();
            $table->string('module_capacity_wp')->nullable();
            $table->string('module_quantity')->nullable();
            $table->string('inspection_status')->nullable();
            $table->string('inspection_return_date')->nullable();
            $table->string('inspection_return_comment')->nullable();
            $table->string('subsidy_amount')->nullable();
            $table->string('subsidy_return_date')->nullable();
            $table->string('subsidy_return_to')->nullable();
            $table->string('subsidy_return_comment')->nullable();
            $table->timestamps();

            $table->unique('sales_master_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_data');
    }
};
