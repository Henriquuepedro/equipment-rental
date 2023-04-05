<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsToPayPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_to_pay_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('bill_to_pay_id');
            $table->integer('parcel');
            $table->integer('due_day');
            $table->date('due_date');
            $table->decimal('due_value', 12);
            $table->integer('payment_id')->nullable();
            $table->string('payment_name', 256)->nullable();
            $table->dateTime('payday')->nullable();
            $table->unsignedBigInteger('user_insert');
            $table->unsignedBigInteger('user_update')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('bill_to_pay_id')->references('id')->on('bill_to_pays');
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
        Schema::dropIfExists('bill_to_pay_payments');
    }
}
