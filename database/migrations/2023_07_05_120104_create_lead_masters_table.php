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
        Schema::create('lead_masters', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->integer('company_profile_id')->default(0);
            $table->integer('manager_id')->default(0);
            $table->integer('assign_id')->default(0);
            $table->string('lead_title');
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('lead_value')->nullable();
            $table->text('notes')->nullable();            
            $table->integer('india_mart_unique_id')->nullable();
            $table->integer('category_id')->default(0);
            $table->integer('product_id')->default(0);            
            $table->integer('source_id')->default(0);
            $table->string('tags')->nullable();
            $table->date('last_contacted')->nullable();
            $table->date('reminder_date')->nullable();
            $table->string('company_name')->nullable();
            $table->integer('state_id')->default(0);
            $table->integer('city_id')->default(0);
            $table->string('pincode')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->integer('status_master_id')->default(0);
            $table->enum('status', ['0','1', '2', '3'])->default(0)->comment('0 = new 1 = done 2 = close 3 = next');
            $table->string('query_type')->nullable();
            $table->string('query_time')->nullable();
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
        Schema::dropIfExists('lead_masters');
    }
};
