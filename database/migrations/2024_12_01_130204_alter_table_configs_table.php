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
        Schema::table('configs', function (Blueprint $table) {
            $table->tinyInteger('multiply_quantity_of_equipment_per_amount')->nullable()->after('view_observation_client_rental');
            $table->tinyInteger('multiply_quantity_of_equipment_per_day')->nullable()->after('view_observation_client_rental');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->dropColumn('multiply_quantity_of_equipment_per_amount');
            $table->dropColumn('multiply_quantity_of_equipment_per_day');
        });
    }
};
