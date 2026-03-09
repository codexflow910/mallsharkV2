<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('store_id')->on('stores');
            $table->string('code')->nullable();
            $table->string('title')->nullable();
            $table->enum('type', ['coupon', 'sale']);
            $table->boolean('platform_wide')->default(false);
            $table->enum('discount_value_type', ['percent', 'fixed']);
            $table->integer('usage_count')->default(0);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('minimum_purchase_amount', 10, 2)->nullable();
            $table->decimal('maximum_purchase_amount', 10, 2)->nullable();
            $table->integer('minimum_quantity_items')->nullable();
            $table->integer('maximum_quantity_items')->nullable();
            $table->integer('max_usage_limit')->nullable();
            $table->boolean('one_use_per_customer')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['active','inActive','expired']);
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
        Schema::dropIfExists('discounts');
    }
}
