<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFedexConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fedex_config', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('account_number');
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
        Schema::dropIfExists('fedex_config');
    }
}
