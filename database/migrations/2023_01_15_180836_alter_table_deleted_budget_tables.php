<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDeletedBudgetTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->boolean('deleted')->after('observation')->default(false);
        });
        Schema::table('budget_equipments', function (Blueprint $table) {
            $table->boolean('deleted')->after('driver_suggestion')->default(false);
        });
        Schema::table('budget_residues', function (Blueprint $table) {
            $table->boolean('deleted')->after('name_residue')->default(false);
        });
        Schema::table('budget_payments', function (Blueprint $table) {
            $table->boolean('deleted')->after('payday')->default(false);
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
            $table->dropColumn('deleted');
        });
        Schema::table('budget_equipments', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('budget_residues', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('budget_payments', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
    }
}
