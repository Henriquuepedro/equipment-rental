<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDeletedRentalTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
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
    }
}
