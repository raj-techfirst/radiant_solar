<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Normalize existing null sub_agent_id to 0 to satisfy NOT NULL + default
        if (Schema::hasTable('user_commissions')) {
            DB::table('user_commissions')->whereNull('sub_agent_id')->update(['sub_agent_id' => 0]);

            Schema::table('user_commissions', function (Blueprint $table) {
                $table->unsignedBigInteger('sub_agent_id')->default(0)->nullable(false)->change();
            });

            // Add composite unique index including deleted_at to play well with soft deletes
            Schema::table('user_commissions', function (Blueprint $table) {
                $table->unique(['user_id', 'effective_date', 'sub_agent_id', 'deleted_at'], 'user_commissions_user_date_agent_deleted_unique');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('user_commissions')) {
            Schema::table('user_commissions', function (Blueprint $table) {
                if (Schema::hasColumn('user_commissions', 'deleted_at')) {
                    $table->dropUnique('user_commissions_user_date_agent_deleted_unique');
                }
                // revert column to nullable
                $table->unsignedBigInteger('sub_agent_id')->nullable()->default(null)->change();
            });
        }
    }
};


