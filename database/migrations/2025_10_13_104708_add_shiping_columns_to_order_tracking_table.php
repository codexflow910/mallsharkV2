<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipingColumnsToOrderTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->string('ship_label_url')->nullable();
            $table->string('ship_service')->nullable();
            $table->decimal('ship_amount', 10, 2)->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('shipped_date')->nullable();
            $table->boolean('shipped_manually')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            //
        });
    }
}
