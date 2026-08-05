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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('company_profile_id')->default(0);
            $table->integer('manager_id')->default(0);
            $table->integer('assign_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('product_id')->default(0);
            $table->string('task_name');
            $table->string('description');
            $table->time('timespand')->nullable();
            $table->string('hours')->nullable();
            $table->string('minutes')->nullable();
            $table->string('time')->nullable();
            $table->date('task_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('priority', ['1', '2', '3'])->default(1)->comment('1 = high 2 = medium 3 = low');
            $table->enum('status', ['1', '2', '3', '4'])->default(1)->comment('1 = pending 2 = in progress 3 = complete 4 = cancel');
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
        Schema::dropIfExists('tasks');
    }
};
