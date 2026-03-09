<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandDistributorIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // $table->unsignedBigInteger('brand_distributor_id')->nullable()->after('store_id');
            $table->unsignedBigInteger('brand_id')->nullable()->after('brand_distributor_id');

            $table->foreign('brand_distributor_id')
                ->references('id')
                ->on('brand_distributors')
                ->onDelete('set null');

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
