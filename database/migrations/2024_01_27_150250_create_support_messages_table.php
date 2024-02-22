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
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('support_id')->unsigned();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('user_created')->unsigned();
            $table->longText('description');
            $table->string('sent_by')->comment('operator|user');
            $table->timestamps();

            $table->foreign('support_id')->references('id')->on('supports');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_created')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_messages');
    }
};
