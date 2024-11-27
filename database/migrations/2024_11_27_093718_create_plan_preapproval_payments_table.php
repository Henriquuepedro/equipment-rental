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
        Schema::create('plan_preapproval_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('plan_payment_id')->unsigned();
            $table->string('preapproval_id', 255);
            $table->string('status_detail', 255);
            $table->string('status', 255);
            $table->decimal('transaction_amount');
            $table->bigInteger('gateway_payment_id');
            $table->dateTime('gateway_debit_date');
            $table->dateTime('gateway_date_created');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('plan_payment_id')->references('id')->on('plan_payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_preapproval_payments');
    }
};
