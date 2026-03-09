<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSamountRefundPriceInProductRefund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_refund', function (Blueprint $table) {
            // $table->float('price')->nullable();
            $table->float('shipping_amount')->nullable();
            // $table->bigInteger('is_refund')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_refund', function (Blueprint $table) {
            // $table->dropColumn('price');
            $table->dropColumn('shipping_amount');
            // $table->dropColumn('is_refund');
        });
    }
}
