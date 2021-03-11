<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_equipments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('budget_id')->unsigned();
            $table->bigInteger('equipment_id')->unsigned();

            $table->string('reference', 256);
            $table->string('name', 256)->nullable();
            $table->integer('volume')->nullable();
            $table->integer('quantity');
            $table->decimal('unitary_value', 12,2)->nullable();
            $table->decimal('total_value', 12,2)->nullable();
            $table->bigInteger('vehicle_suggestion')->unsigned()->nullable();
            $table->bigInteger('driver_suggestion')->unsigned()->nullable();
            $table->tinyInteger('use_date_diff_equip');

            $table->dateTime('expected_delivery_date');
            $table->dateTime('expected_withdrawal_date')->nullable();
            $table->tinyInteger('not_use_date_withdrawal');

            $table->bigInteger('user_insert')->unsigned();
            $table->bigInteger('user_update')->unsigned()->nullable();

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipments');
            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->foreign('user_insert')->references('id')->on('users');
            $table->foreign('user_update')->references('id')->on('users');
            $table->foreign('vehicle_suggestion')->references('id')->on('vehicles');
            $table->foreign('driver_suggestion')->references('id')->on('drivers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budget_equipments');
    }
}
