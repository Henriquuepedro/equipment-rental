<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddActualDriverVehicleRentalEquipmentsWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_equipments', function (Blueprint $table) {
            $table->unsignedBigInteger('actual_driver_withdrawal')->after('actual_driver_delivery')->nullable();
            $table->unsignedBigInteger('actual_vehicle_withdrawal')->after('actual_driver_delivery')->nullable();

            $table->foreign('actual_vehicle_withdrawal')->references('id')->on('vehicles');
            $table->foreign('actual_driver_withdrawal')->references('id')->on('drivers');
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
            $table->dropForeign('rental_equipments_actual_vehicle_withdrawal_foreign');
            $table->dropForeign('rental_equipments_actual_driver_withdrawal_foreign');
            $table->dropColumn('actual_driver_withdrawal');
            $table->dropColumn('actual_vehicle_withdrawal');
        });
    }
}
