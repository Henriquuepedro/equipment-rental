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
        Schema::table('bill_to_pays', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('bill_to_pay_payments', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('rental_equipments', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('rental_residues', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('rental_payments', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bill_to_pays', function (Blueprint $table) {
            $table->boolean('deleted')->after('observation')->default(false);
        });
        Schema::table('bill_to_pay_payments', function (Blueprint $table) {
            $table->boolean('deleted')->after('payday')->default(false);
        });
        Schema::table('rentals', function (Blueprint $table) {
            $table->boolean('deleted')->after('observation')->default(false);
        });
        Schema::table('rental_equipments', function (Blueprint $table) {
            $table->boolean('deleted')->after('actual_driver_withdrawal')->default(false);
        });
        Schema::table('rental_residues', function (Blueprint $table) {
            $table->boolean('deleted')->after('name_residue')->default(false);
        });
        Schema::table('rental_payments', function (Blueprint $table) {
            $table->boolean('deleted')->after('payday')->default(false);
        });
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
};
