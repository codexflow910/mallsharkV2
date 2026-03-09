<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefundedByToStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->enum('cancelled_by', ['user', 'seller', 'admin'])->nullable()->after('reject_date');
        });
    }

    public function down()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn('cancelled_by');
        });
    }
}
