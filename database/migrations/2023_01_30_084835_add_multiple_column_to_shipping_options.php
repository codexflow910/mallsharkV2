<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleColumnToShippingOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_options', function (Blueprint $table) {
            $table->integer('shipping_state_id')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_address1')->nullable();
            $table->string('shipping_address2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_options', function (Blueprint $table) {
            $table->dropColumn('shipping_amount', 'shipping_city', 'shipping_address1', 'shipping_address2');
           
        });
    }
}
