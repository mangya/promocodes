<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('longitude');
            $table->string('latitude');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->tinyInteger('status');
            $table->string('promocode', 10)->nullable();
            $table->bigInteger('promocode_id')->nullable();
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
        Schema::dropIfExists('rides');
    }
}
