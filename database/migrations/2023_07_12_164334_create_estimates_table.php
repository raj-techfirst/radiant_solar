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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_master_id')->constrained()->onDelete('cascade');
            $table->integer('company_profile_id')->default(0);
            $table->integer('manager_id')->default(0);
            $table->integer('assign_id')->default(0);                   
            $table->integer('user_id')->default(0);
            $table->string('estimate_title')->nullable();
            $table->date('estimate_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('remark')->nullable();
            $table->double('subtotal',[8,2]);
            $table->double('discount',[8,2]);
            $table->double('total',[8,2]);
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
        Schema::dropIfExists('estimates');
    }
};
