<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoneStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zone_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->integer('state_id');
            $table->timestamps();

             // Foreign key to zones
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');

            // Foreign key to states
            $table->foreign('state_id')->references('state_id')->on('states')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zone_states');
    }
}
