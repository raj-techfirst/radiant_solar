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
            $table->enum('file_type', ['C', 'L'])->default('C');
            $table->enum('apply_for_loan', ['0', '1'])->default(0);
            $table->enum('login', ['0', '1'])->default(0);
            $table->enum('loan_sension', ['0', '1'])->default(0);
            $table->enum('disbursement', ['0', '1'])->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
