<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('effective_date');
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('installation', 12, 2)->default(0);
            $table->unsignedBigInteger('sub_agent_id')->nullable(); // references company_profiles.id
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('sub_agent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_commissions');
    }
};


