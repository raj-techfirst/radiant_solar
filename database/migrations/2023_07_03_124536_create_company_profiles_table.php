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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('parent_id')->nullable();
            $table->integer('manager_id')->nullable();
            $table->enum('user_type', ['O', 'M', 'S'])->default('O')->comment('O = Owner M = Manager S = Sales');
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->string('indiamart_key')->nullable();
            $table->string('justdial_key')->nullable();
            $table->integer('is_indiamart')->default(0);
            $table->integer('is_justdial')->default();
            $table->string('business_name');
            $table->string('address');
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
        Schema::dropIfExists('company_profiles');
    }
};
