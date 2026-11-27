<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_data', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_data', 'solar_plant_installation_date')) {
                $table->string('solar_plant_installation_date')->nullable()->after('vendor_consumer_agreement_uploading_date');
            }

            if (! Schema::hasColumn('portal_data', 'loan_taken')) {
                $table->string('loan_taken')->nullable()->after('consumer_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portal_data', function (Blueprint $table) {
            if (Schema::hasColumn('portal_data', 'solar_plant_installation_date')) {
                $table->dropColumn('solar_plant_installation_date');
            }

            if (Schema::hasColumn('portal_data', 'loan_taken')) {
                $table->dropColumn('loan_taken');
            }
        });
    }
};
