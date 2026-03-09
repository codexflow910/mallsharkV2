<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_shippings', function (Blueprint $table) {
            $table->id();
            $table->integer('shipping_profile_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('shipping_amount')->nullable();
            $table->integer('base_price')->nullable();
            $table->integer('handlilng_fee')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('shipping_method')->nullable();
            $table->integer('service_id')->nullable();
            $table->string('service_slug')->nullable();
            $table->string('service_name')->nullable();
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
        Schema::dropIfExists('order_shippings');
    }
}
