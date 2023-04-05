<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentalResiduesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_residues', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('rental_id')->unsigned();
            $table->bigInteger('residue_id')->unsigned();
            $table->string('name_residue', 256);
            $table->bigInteger('user_insert')->unsigned();
            $table->bigInteger('user_update')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('rental_id')->references('id')->on('rentals');
            $table->foreign('residue_id')->references('id')->on('residues');
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
        Schema::dropIfExists('rental_residues');
    }
}
