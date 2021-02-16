<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('code');
            $table->bigInteger('company_id')->unsigned();
            $table->tinyInteger('type_rental')->default(0);

            $table->bigInteger('client_id')->unsigned();

            $table->string('address_zipcode', 8)->nullable();
            $table->string('address_name', 256);
            $table->string('address_number', 256);
            $table->string('address_complement', 256)->nullable();
            $table->string('address_reference', 256)->nullable();
            $table->string('address_neigh', 256);
            $table->string('address_city', 256);
            $table->string('address_state', 256);
            $table->string('address_lat', 64);
            $table->string('address_lng', 64);

            $table->dateTime('expected_delivery_date');
            $table->dateTime('expected_withdrawal_date')->nullable();
            $table->tinyInteger('not_use_date_withdrawal');

            $table->decimal('gross_value', 12,2)->nullable();
            $table->decimal('extra_value', 12,2)->nullable();
            $table->decimal('discount_value', 12,2)->nullable();
            $table->decimal('net_value', 12,2)->nullable();

            $table->tinyInteger('calculate_net_amount_automatic')->nullable();
            $table->tinyInteger('use_parceled')->nullable();
            $table->tinyInteger('automatic_parcel_distribution')->nullable();

            $table->longText('observation')->nullable();

            $table->bigInteger('user_insert')->unsigned();
            $table->bigInteger('user_update')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients');
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
        Schema::dropIfExists('rentals');
    }
}
