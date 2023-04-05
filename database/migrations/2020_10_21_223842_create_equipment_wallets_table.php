<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_wallets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('equipment_id')->unsigned();
            $table->integer('day_start');
            $table->integer('day_end')->nullable();
            $table->decimal('value', 12, 2);
            $table->boolean('active')->default(true);
            $table->bigInteger('user_insert')->unsigned();
            $table->bigInteger('user_update')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
            $table->foreign('user_insert')->references('id')->on('users');
            $table->foreign('user_update')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_wallets');
    }
}
