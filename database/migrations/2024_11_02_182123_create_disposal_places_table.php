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
        Schema::create('disposal_places', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->string('name', 256);
            $table->string('fantasy', 256)->nullable();
            $table->string('type_person', 2);
            $table->string('cpf_cnpj', 14);
            $table->string('rg_ie', 16)->nullable();
            $table->string('email', 256)->nullable();
            $table->string('phone_1', 11)->nullable();
            $table->string('phone_2', 11)->nullable();
            $table->string('contact', 256)->nullable();
            $table->string('address_zipcode', 8)->nullable();
            $table->string('address_name', 256)->nullable();
            $table->string('address_number', 256)->nullable();
            $table->string('address_complement', 256)->nullable();
            $table->string('address_reference', 256)->nullable();
            $table->string('address_neigh', 256)->nullable();
            $table->string('address_city', 256)->nullable();
            $table->string('address_state', 256)->nullable();
            $table->longText('observation')->nullable();
            $table->boolean('active')->default(1);
            $table->bigInteger('user_insert')->unsigned();
            $table->bigInteger('user_update')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
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
        Schema::dropIfExists('disposal_places');
    }
};
