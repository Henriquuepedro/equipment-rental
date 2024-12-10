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
        Schema::table('plan_preapproval_payments', function (Blueprint $table) {
            $table->dateTime('gateway_debit_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_preapproval_payments', function (Blueprint $table) {
            $table->dateTime('gateway_debit_date')->change();
        });
    }
};
