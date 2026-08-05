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
        Schema::create('paymet_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_master_id')->constrained()->onDelete('cascade');
            $table->enum('payment_tpye',['Cash','Cheque','NEFT','UPI','RTGS'])->default('Cash');
            $table->string('amount')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('utr_number')->nullable();
            $table->string('upi_id')->nullable();
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
        Schema::dropIfExists('paymet_collections');
    }
};
