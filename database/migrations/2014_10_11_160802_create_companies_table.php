<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256);
            $table->string('fantasy', 256)->nullable();
            $table->string('type_person', 2);
            $table->string('cpf_cnpj', 14);
            $table->string('email', 256);
            $table->string('phone_1', 11);
            $table->string('phone_2', 11)->nullable();
            $table->string('contact', 256);
            $table->string('logo', 64)->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('address', 256)->nullable();
            $table->string('number', 256)->nullable();
            $table->string('complement', 256)->nullable();
            $table->string('reference', 256)->nullable();
            $table->string('neigh', 256)->nullable();
            $table->string('city', 256)->nullable();
            $table->string('state', 256)->nullable();
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
        Schema::dropIfExists('companies');
    }
}
