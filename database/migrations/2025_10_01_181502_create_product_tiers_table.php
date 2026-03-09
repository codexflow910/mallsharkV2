<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_tiers', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('min_quantity');
            $table->integer('max_quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('shipping_price', 10, 2);
            $table->integer('variation_id')->nullable();
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
        Schema::dropIfExists('product_tiers');
    }
}
