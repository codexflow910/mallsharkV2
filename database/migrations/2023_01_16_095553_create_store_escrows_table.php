<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreEscrowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_escrows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('store_id')->nullable();
            $table->string('store_wallet_address')->nullable();
            $table->double('order_price')->nullable();
            $table->double('payable_amount')->nullable();
            $table->double('ship_price')->nullable();
            $table->double('order_price_with_out_shipping')->nullable();
            $table->double('profit')->nullable();
            $table->string('status')->nullable();
            $table->string('profit_status')->nullable();
            $table->date('payment_date')->nullable();
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
        Schema::dropIfExists('store_escrows');
    }
}
