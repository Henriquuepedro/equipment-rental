<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillToPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_to_pays', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('code');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('provider_id');

            $table->decimal('gross_value', 12)->nullable();
            $table->decimal('extra_value', 12)->nullable();
            $table->decimal('discount_value', 12)->nullable();
            $table->decimal('net_value', 12)->nullable();

            $table->tinyInteger('calculate_net_amount_automatic')->nullable();
            $table->tinyInteger('use_parceled')->nullable();
            $table->tinyInteger('automatic_parcel_distribution')->nullable();

            $table->longText('form_payment')->nullable();
            $table->longText('observation')->nullable();

            $table->unsignedBigInteger('user_insert');
            $table->unsignedBigInteger('user_update')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('providers');
            $table->foreign('provider_id')->references('id')->on('form_payments');
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
        Schema::dropIfExists('bill_to_pays');
    }
}
