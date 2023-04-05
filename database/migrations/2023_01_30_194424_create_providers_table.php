<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 256);
            $table->string('fantasy', 256)->nullable();
            $table->string('email', 256)->nullable();
            $table->string('phone_1', 11)->nullable();
            $table->string('phone_2', 11)->nullable();
            $table->string('cpf_cnpj', 14)->nullable();
            $table->string('rg_ie', 16)->nullable();
            $table->string('type', 2);
            $table->longText('observation')->nullable();
            $table->string('contact', 256)->nullable();
            $table->unsignedBigInteger('marital_status')->nullable();
            $table->unsignedBigInteger('nationality')->nullable();
            $table->date('birth_date')->nullable();
            $table->tinyInteger('sex')->nullable();

            $table->string('address', 256)->nullable();
            $table->string('number', 256)->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('complement', 256)->nullable();
            $table->string('reference', 256)->nullable();
            $table->string('neigh', 256)->nullable();
            $table->string('city', 256)->nullable();
            $table->string('state', 256)->nullable();

            $table->unsignedBigInteger('user_insert');
            $table->unsignedBigInteger('user_update')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_insert')->references('id')->on('users');
            $table->foreign('user_update')->references('id')->on('users');
            $table->foreign('nationality')->references('id')->on('nationalities');
            $table->foreign('marital_status')->references('id')->on('marital_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers');
    }
}
