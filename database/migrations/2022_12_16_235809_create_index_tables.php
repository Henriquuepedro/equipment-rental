<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->index(['company_id','id','name','reference','volume'], 'company_id_name_reference_volume');
            $table->index(['id','company_id'], 'id_company');
        });

        Schema::table('equipment_wallets', function (Blueprint $table) {
            $table->index(['equipment_id','company_id','day_start','day_end'], 'equipment_company_day_start_end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropIndex('company_id_name_reference_volume');
            $table->dropIndex('id_company');
        });

        Schema::table('equipment_wallets', function (Blueprint $table) {
            $table->dropIndex('equipment_company_day_start_end');
        });
    }
}
