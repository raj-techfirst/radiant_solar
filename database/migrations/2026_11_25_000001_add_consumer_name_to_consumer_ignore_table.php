<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('consumer_ignore', 'consumer_name')) {
            Schema::table('consumer_ignore', function (Blueprint $table) {
                $table->string('consumer_name')->nullable()->after('consumer_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('consumer_ignore', 'consumer_name')) {
            Schema::table('consumer_ignore', function (Blueprint $table) {
                $table->dropColumn('consumer_name');
            });
        }
    }
};
