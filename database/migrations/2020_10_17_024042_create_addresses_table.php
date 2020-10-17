<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->foreign('company_id')->references('id')->on('companies');
            $table->integer('client_id')->foreign('client_id')->references('id')->on('clients');
            $table->string('name_address', 256)->nullable();
            $table->string('address', 256)->nullable();
            $table->string('number', 256)->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('complement', 256)->nullable();
            $table->string('reference', 256)->nullable();
            $table->string('neigh', 256)->nullable();
            $table->string('city', 256)->nullable();
            $table->string('state', 256)->nullable();
            $table->integer('user_insert')->foreign('user_insert')->references('id')->on('users');
            $table->integer('user_update')->foreign('user_update')->references('id')->on('users')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
