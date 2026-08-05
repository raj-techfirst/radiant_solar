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
            $table->enum('application_pending', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('pending_approvel', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('document_verified', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('feasibility_approved', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('payment_receveid', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('dispach_pending_list', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('installation_pending', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('installation_done', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('module_details', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('invater_details', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('structure', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('cable', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('meter_application_done', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('meter_installation', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('subsidy_claimed', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('subsidy_receveid', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
            $table->enum('file_cancel_order', ['0', '1'])->default(0)->comment('0 = Pending 1 = Approve');
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
