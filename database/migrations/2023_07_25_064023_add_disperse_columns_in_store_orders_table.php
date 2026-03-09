<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisperseColumnsInStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dateTime('shipped_date')->nullable()->after('payment_status');
            $table->float('platform_fee')->nullable()->after('payment_status');
            $table->float('payable_amount')->nullable()->after('payment_status');
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
            $table->dropColumn('platform_fee');
            $table->dropColumn('payable_amount');
            $table->dropColumn('shipped_date');
        });
    }
}
