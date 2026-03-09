<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_refund', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('buyer_id')->nullable();
            $table->bigInteger('store_id')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->bigInteger('price')->nullable();
            $table->string('reason_type')->nullable();
            $table->string('reason')->nullable();
            $table->string('reject_order_reason')->nullable();
            $table->bigInteger('is_complete')->nullable();
            $table->bigInteger('is_reject')->nullable();
            $table->string('is_refund')->nullable();
            $table->string('refund_date')->nullable();
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
        Schema::dropIfExists('product_refund');
    }
}
