<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableFieldsClientsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('marital_status')->after('contact')->nullable();
            $table->unsignedBigInteger('nationality')->after('contact')->nullable();
            $table->date('birth_date')->after('contact')->nullable();
            $table->tinyInteger('sex')->after('contact')->nullable();

            $table->foreign('nationality')->references('id')->on('nationalities');
            $table->foreign('marital_status')->references('id')->on('marital_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign('clients_marital_status_foreign');
            $table->dropForeign('clients_nationality_foreign');
            $table->dropColumn('sex');
            $table->dropColumn('birth_date');
            $table->dropColumn('nationality');
            $table->dropColumn('marital_status');
        });
    }
}
