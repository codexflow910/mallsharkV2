<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopifyColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('shopify_inventory_item_id')->after('discount_id')->nullable();
            $table->boolean('is_shopify_import')->after('shopify_inventory_item_id')->default(false);
        });

        Schema::table('product_variation', function (Blueprint $table) {
            $table->string('shopify_inventory_item_id')->after('sales')->nullable();

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
            $table->dropColumn('shopify_inventory_item_id');
            $table->dropColumn('is_shopify_import');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn('shopify_inventory_item_id');
        });
    }
}
