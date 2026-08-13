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
        Schema::table('sales_masters', function (Blueprint $table) {
            $table->enum('project_completion', ['0', '1'])->default('0')->after('subsidy_receveid')->comment('0 = Not Completed, 1 = Project Completed');
            $table->date('project_completion_date')->nullable()->after('project_completion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_masters', function (Blueprint $table) {
            $table->dropColumn('project_completion');
            $table->dropColumn('project_completion_date');
        });
    }
};