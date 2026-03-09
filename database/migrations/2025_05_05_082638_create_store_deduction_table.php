<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreDeductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_deduction', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->decimal('mall_shark_amount', 10, 2)->default(0);
            $table->boolean('shipping_check')->default(false);
            $table->boolean('ads_check')->default(false);
            $table->decimal('ad_amount', 10, 2)->default(0);
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
        Schema::dropIfExists('store_deduction');
    }
}
