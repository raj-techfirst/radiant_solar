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
        Schema::create('sales_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('consumer_number');
            $table->date('master_create_date');
            $table->enum('consumer_type',['Resident','Industrial','Commercial','Trading'])->default('Resident');
            $table->string('consumer_name');
            $table->string('gst_number')->nullable();
            $table->string('district_id');
            $table->string('taluka_id');
            $table->string('village_id');
            $table->text('address')->nullable();
            $table->string('pin_code');
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('contracted_load')->nullable();
            $table->string('phase')->nullable();
            $table->string('division')->nullable();
            $table->string('sub_division')->nullable();
            $table->string('circle')->nullable();
            $table->string('discom');
            $table->string('reference')->nullable();
            $table->string('agent_sales_person');
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('sales_masters');
    }
};
