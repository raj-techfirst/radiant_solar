<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumer_ignore', function (Blueprint $table) {
            $table->id();
            $table->string('consumer_number')->unique();
            $table->date('ignore_date');
            $table->timestamp('timestep')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumer_ignore');
    }
};
