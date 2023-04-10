<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDatesBudgetEquipmentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_equipments', function (Blueprint $table) {
            $table->tinyInteger('not_use_date_withdrawal')->after('driver_suggestion');
            $table->dateTime('expected_withdrawal_date')->nullable()->after('driver_suggestion');
            $table->dateTime('expected_delivery_date')->after('driver_suggestion');
            $table->tinyInteger('use_date_diff_equip')->after('driver_suggestion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_equipments', function (Blueprint $table) {
            $table->dropColumn('not_use_date_withdrawal');
            $table->dropColumn('expected_withdrawal_date');
            $table->dropColumn('expected_delivery_date');
            $table->dropColumn('use_date_diff_equip');
        });
    }
}
