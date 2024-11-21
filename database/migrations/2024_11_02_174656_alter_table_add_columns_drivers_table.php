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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('address_zipcode', 8)->after('observation')->nullable();
            $table->string('address_name', 256)->after('address_zipcode')->nullable();
            $table->string('address_number', 256)->after('address_name')->nullable();
            $table->string('address_complement', 256)->after('address_number')->nullable();
            $table->string('address_reference', 256)->after('address_complement')->nullable();
            $table->string('address_neigh', 256)->after('address_reference')->nullable();
            $table->string('address_city', 256)->after('address_neigh')->nullable();
            $table->string('address_state', 256)->after('address_city')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('address_zipcode');
            $table->dropColumn('address_name');
            $table->dropColumn('address_number');
            $table->dropColumn('address_complement');
            $table->dropColumn('address_reference');
            $table->dropColumn('address_neigh');
            $table->dropColumn('address_city');
            $table->dropColumn('address_state');
        });
    }
};
