<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDeletedBillToPaysTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_to_pays', function (Blueprint $table) {
            $table->boolean('deleted')->after('observation')->default(false);
        });
        Schema::table('bill_to_pay_payments', function (Blueprint $table) {
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
        Schema::table('bill_to_pays', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
        Schema::table('bill_to_pay_payments', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
    }
}
