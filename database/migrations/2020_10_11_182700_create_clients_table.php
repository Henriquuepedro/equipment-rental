<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->foreign('company_id')->references('id')->on('companies')->nullable();;
            $table->string('type', 2);
            $table->string('name', 256);
            $table->string('fantasy', 256)->nullable();
            $table->string('email', 256)->nullable();
            $table->string('phone_1', 11)->nullable();
            $table->string('phone_2', 11)->nullable();
            $table->string('cpf_cnpj', 14)->nullable();
            $table->string('rg_ie', 16)->nullable();
            $table->integer('user_insert');
            $table->integer('user_update')->nullable();
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
        Schema::dropIfExists('clients');
    }
}
