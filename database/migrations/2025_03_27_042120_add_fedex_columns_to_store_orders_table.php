<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFedexColumnsToStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->string('ship_label_url')->nullable();
            $table->decimal('fedex_ship_amount', 10, 2)->nullable();
            $table->string('ship_service')->nullable();
            $table->date('delivery_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn(['ship_label_url', 'tracking_number', 'fedex_ship_amount', 'ship_service', 'delivery_date']);
        });
    }
}
