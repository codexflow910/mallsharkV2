<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleColumnToOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('shipment_id')->nullable();
            $table->string('rate_id')->nullable();
            $table->string('label_url')->nullable();
            $table->string('lable_pdf_url')->nullable();
            $table->string('tracking_id')->nullable();
            $table->string('tracking_code')->nullable();
            $table->longText('shipment_object')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('shipment_id', 'rate_id', 'label_url', 'lable_pdf_url', 'tracking_code','shipment_object');
        });
    }
}
