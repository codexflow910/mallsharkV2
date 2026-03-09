<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUspsConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usps_config', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('crid');
            $table->string('mailer_id');
            $table->string('eps_number');
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
        Schema::dropIfExists('usps_config');
    }
}
