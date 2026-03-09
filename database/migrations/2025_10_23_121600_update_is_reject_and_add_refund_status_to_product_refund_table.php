<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIsRejectAndAddRefundStatusToProductRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_refund', function (Blueprint $table) {
            $table->boolean('is_reject')->default(0)->change();
            $table->boolean('refund_status')->default(0)->after('is_refund');
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
            //
        });
    }
}
