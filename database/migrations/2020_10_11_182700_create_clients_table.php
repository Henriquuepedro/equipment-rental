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
            $table->integer('company_id');
            $table->string('type', 2);
            $table->string('name', 256);
            $table->string('fantasy', 256);
            $table->string('email', 256);
            $table->string('phone_1', 11);
            $table->string('phone_2', 11);
            $table->string('cpf', 11);
            $table->string('cnpj', 14);
            $table->string('rg', 16);
            $table->string('ie', 16);
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
