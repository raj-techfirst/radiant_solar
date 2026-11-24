<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_quatations', function (Blueprint $table) {
            $table->boolean('created_from_unmatched_preview')->default(false)->after('form_type');
        });
    }

    public function down(): void
    {
        Schema::table('sales_quatations', function (Blueprint $table) {
            $table->dropColumn('created_from_unmatched_preview');
        });
    }
};
