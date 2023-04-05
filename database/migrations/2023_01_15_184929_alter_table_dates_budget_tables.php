<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDatesBudgetTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->tinyInteger('not_use_date_withdrawal')->after('observation');
            $table->dateTime('expected_withdrawal_date')->nullable()->after('observation');
            $table->dateTime('expected_delivery_date')->after('observation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('not_use_date_withdrawal');
            $table->dropColumn('expected_withdrawal_date');
            $table->dropColumn('expected_delivery_date');
        });
    }
}
