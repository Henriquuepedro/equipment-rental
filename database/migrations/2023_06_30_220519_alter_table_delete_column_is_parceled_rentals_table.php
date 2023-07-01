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
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('use_parceled');
        });
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('use_parceled');
        });
        Schema::table('bill_to_pays', function (Blueprint $table) {
            $table->dropColumn('use_parceled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->tinyInteger('use_parceled')->nullable();
        });
        Schema::table('budgets', function (Blueprint $table) {
            $table->tinyInteger('use_parceled')->nullable();
        });
        Schema::table('bill_to_pays', function (Blueprint $table) {
            $table->tinyInteger('use_parceled')->nullable();
        });
    }
};
