<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTradingToLeadMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('lead_masters', 'is_trading')) {
            Schema::table('lead_masters', function (Blueprint $table) {
                $table->enum('is_trading', ['0', '1'])->default('0')->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('lead_masters', 'is_trading')) {
            Schema::table('lead_masters', function (Blueprint $table) {
                $table->dropColumn('is_trading');
            });
        }
    }
}
