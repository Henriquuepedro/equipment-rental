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
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('receiver_whatsapp_phone_1')->default(0)->after('phone_1');
            $table->boolean('whatsapp_phone_1')->default(0)->after('phone_1');
            $table->boolean('receiver_whatsapp_phone_2')->default(0)->after('phone_2');
            $table->boolean('whatsapp_phone_2')->default(0)->after('phone_2');
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
            $table->dropColumn('receiver_whatsapp_phone_1');
            $table->dropColumn('whatsapp_phone_1');
            $table->dropColumn('receiver_whatsapp_phone_2');
            $table->dropColumn('whatsapp_phone_2');
        });
    }
};
