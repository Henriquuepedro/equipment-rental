<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256);
            $table->string('username', 256)->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 11)->nullable();
            $table->string('password', 256);
            $table->integer('company_id')->foreign('company_id')->references('id')->on('companies')->nullable();
            $table->rememberToken();
            $table->timestamps();

//            $table->foreign('company_id')->references('id')->on('companies');
            $table->index('company_id', 'company_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
