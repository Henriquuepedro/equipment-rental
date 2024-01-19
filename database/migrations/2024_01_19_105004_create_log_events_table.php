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
        Schema::create('log_events', function (Blueprint $table) {
            $table->id();
            $table->string('event');
            $table->foreignId('user_id')->nullable();
            $table->dateTime('event_date');
            $table->ipAddress('ip');
            $table->morphs('auditable');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_events');
    }
};
