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
        Schema::create('equipment_rental_mtrs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('rental_mtr_id')->unsigned();
            $table->bigInteger('rental_equipment_id')->unsigned();
            $table->bigInteger('residue_id')->unsigned();
            $table->string('quantity');
            $table->string('classification')->nullable();
            $table->dateTime('date');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('rental_mtr_id')->references('id')->on('rental_mtrs')->onDelete('cascade');
            $table->foreign('rental_equipment_id')->references('id')->on('rental_equipments')->onDelete('cascade');
            $table->foreign('residue_id')->references('id')->on('residues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_rental_mtrs');
    }
};
