<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->string('name')->default('General Shipping');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->integer('state_id')->nullable();
            $table->string('zip_code')->nullable();
            $table->boolean('is_general')->default(false);
            $table->timestamps();

            $table->foreign('store_id')->references('store_id')->on('stores')->onDelete('cascade');
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
        Schema::dropIfExists('shipping_profiles');
    }
}
