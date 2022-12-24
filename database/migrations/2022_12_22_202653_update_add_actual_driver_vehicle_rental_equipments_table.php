<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddActualDriverVehicleRentalEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_equipments', function (Blueprint $table) {
            $table->unsignedBigInteger('actual_driver_delivery')->after('actual_withdrawal_date')->nullable();
            $table->unsignedBigInteger('actual_vehicle_delivery')->after('actual_withdrawal_date')->nullable();

            $table->foreign('actual_vehicle_delivery')->references('id')->on('vehicles');
            $table->foreign('actual_driver_delivery')->references('id')->on('drivers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rental_equipments', function (Blueprint $table) {
            $table->dropForeign('rental_equipments_actual_vehicle_delivery_foreign');
            $table->dropForeign('rental_equipments_actual_driver_delivery_foreign');
            $table->dropColumn('actual_driver_delivery');
            $table->dropColumn('actual_vehicle_delivery');
        });
    }
}
