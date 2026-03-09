<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_shippings', function (Blueprint $table) {
            $table->id();
            $table->enum('profile_type', ['general', 'custom']);
            $table->unsignedBigInteger('type_id');
            
            $table->boolean('shipping_per_item');
            $table->decimal('per_item_price', 10, 2)->nullable();
            $table->integer('max_items_allowed_for_price')->nullable();
            $table->decimal('additional_price', 10, 2)->nullable();

            $table->boolean('shipping_by_weight');
            $table->decimal('base_price', 10, 2)->nullable();
            $table->string('weight_unit')->nullable(); // e.g., kg, lb
            $table->decimal('base_price_per_weight', 10, 2)->nullable();
            $table->decimal('max_weight_allowed_for_price', 10, 2)->nullable();
            $table->decimal('additional_price_weight', 10, 2)->nullable();

            $table->string('expected_delivery_time')->nullable();
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
        Schema::dropIfExists('fixed_shippings');
    }
}
