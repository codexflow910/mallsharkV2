<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipstationConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipstation_config', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->timestamps();
            
            $table->unique('store_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipstation_config');
    }
}
