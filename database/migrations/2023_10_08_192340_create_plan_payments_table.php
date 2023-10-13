<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_transaction');
            $table->string('link_billet', 255)->nullable();
            $table->string('barcode_billet', 128)->nullable();
            $table->dateTime('date_of_expiration')->nullable();
            $table->text('key_pix')->nullable();
            $table->longText('base64_key_pix')->nullable();
            $table->string('payment_method_id', 255)->nullable();
            $table->string('payment_type_id', 255)->nullable();
            $table->string('plan', 255);
            $table->bigInteger('plan_id')->unsigned();
            $table->string('status_detail', 255);
            $table->integer('installments')->nullable();
            $table->string('status', 255);
            $table->decimal('gross_amount');
            $table->decimal('net_amount');
            $table->decimal('client_amount');
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('user_created')->unsigned();
            $table->bigInteger('user_updated')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_created')->references('id')->on('users');
            $table->foreign('user_updated')->references('id')->on('users');
            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_payments');
    }
}
