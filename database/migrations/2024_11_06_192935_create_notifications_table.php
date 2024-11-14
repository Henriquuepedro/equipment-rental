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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('user_insert')->unsigned()->nullable();
            $table->bigInteger('user_update')->unsigned()->nullable();
            $table->timestamp('expires_in')->nullable();
            $table->bigInteger('only_permission')->unsigned()->nullable();
            $table->boolean('read')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->bigInteger('user_read_by')->unsigned()->nullable();
            $table->string('title', 256);
            $table->longText('description');
            $table->string('title_icon', 32);
            $table->boolean('active')->default(1);

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('only_permission')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('user_insert')->references('id')->on('users');
            $table->foreign('user_update')->references('id')->on('users');
            $table->foreign('user_read_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
