<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreShippingCarriers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->unsignedBigInteger('shipping_carrier_id');
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->foreign('shipping_carrier_id')->references('id')->on('shipping_carriers')->onDelete('cascade');
            $table->unique(['store_id', 'shipping_carrier_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_shipping_carriers');
    }
}
