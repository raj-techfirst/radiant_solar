<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_payments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            if (Schema::hasColumn('commission_payments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};


