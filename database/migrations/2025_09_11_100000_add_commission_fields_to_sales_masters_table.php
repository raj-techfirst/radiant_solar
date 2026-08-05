<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_masters', function (Blueprint $table) {
            $table->decimal('commission_amount', 15, 2)->default(0)->after('agent_sales_person_id');
            $table->decimal('sub_commission_amount', 15, 2)->default(0)->after('commission_amount');
            $table->decimal('installation_amount', 15, 2)->default(0)->after('sub_commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_masters', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'sub_commission_amount', 'installation_amount']);
        });
    }
};


