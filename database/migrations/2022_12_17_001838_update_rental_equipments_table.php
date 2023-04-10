<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRentalEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_equipments', function (Blueprint $table) {
            $table->dateTime('actual_withdrawal_date')->after('not_use_date_withdrawal')->nullable();
            $table->dateTime('actual_delivery_date')->after('not_use_date_withdrawal')->nullable();
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
            $table->dropColumn('actual_delivery_date');
            $table->dropColumn('actual_withdrawal_date');
        });
    }
}
