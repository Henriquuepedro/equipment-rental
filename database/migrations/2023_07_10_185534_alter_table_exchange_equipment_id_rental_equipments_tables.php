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
        Schema::table('rental_equipments', function (Blueprint $table) {
            $table->unsignedBigInteger('exchange_rental_equipment_id')->after('actual_driver_withdrawal')->nullable();
            $table->boolean('exchanged')->after('exchange_rental_equipment_id')->default(false);

            $table->foreign('exchange_rental_equipment_id')->references('id')->on('rental_equipments');
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
            $table->dropForeign('rental_equipments_exchange_rental_equipment_id_foreign');
            $table->dropColumn('exchange_rental_equipment_id');
            $table->dropColumn('exchanged');
        });
    }
};
