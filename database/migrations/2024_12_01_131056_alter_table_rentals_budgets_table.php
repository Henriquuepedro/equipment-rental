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
        Schema::table('rentals', function (Blueprint $table) {
            $table->tinyInteger('multiply_quantity_of_equipment_per_amount')->default(0)->after('automatic_parcel_distribution');
            $table->tinyInteger('multiply_quantity_of_equipment_per_day')->default(0)->after('automatic_parcel_distribution');
        });
        Schema::table('budgets', function (Blueprint $table) {
            $table->tinyInteger('multiply_quantity_of_equipment_per_amount')->default(0)->after('not_use_date_withdrawal');
            $table->tinyInteger('multiply_quantity_of_equipment_per_day')->default(0)->after('not_use_date_withdrawal');
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
            $table->dropColumn('multiply_quantity_of_equipment_per_amount');
            $table->dropColumn('multiply_quantity_of_equipment_per_day');
        });
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('multiply_quantity_of_equipment_per_amount');
            $table->dropColumn('multiply_quantity_of_equipment_per_day');
        });
    }
};
