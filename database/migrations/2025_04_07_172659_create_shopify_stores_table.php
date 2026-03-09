<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('shop_id')->nullable();
            $table->string('shop_url');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('domain')->nullable();
            $table->string('access_token');
            $table->string('currency_code')->nullable();
            $table->boolean('product_management')->default(false);
            $table->boolean('inventory_management')->default(false);
            $table->boolean('order_synchronization')->default(false);
            $table->boolean('discount_promotion_management')->default(false);
            $table->boolean('shipping_fulfillment')->default(false);
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
        Schema::dropIfExists('shopify_stores');
    }
}
