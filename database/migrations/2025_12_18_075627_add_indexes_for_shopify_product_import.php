<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForShopifyProductImport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add index for shopify_product_id if it doesn't exist
            if (!$this->hasIndex('products', 'products_shopify_product_id_index')) {
                $table->index('shopify_product_id', 'products_shopify_product_id_index');
            }
            
            // Add index for shopify_inventory_item_id if it doesn't exist
            if (!$this->hasIndex('products', 'products_shopify_inventory_item_id_index')) {
                $table->index('shopify_inventory_item_id', 'products_shopify_inventory_item_id_index');
            }
        });

        Schema::table('product_variation', function (Blueprint $table) {
            // Add index for shopify_inventory_item_id if it doesn't exist
            if (!$this->hasIndex('product_variation', 'product_variation_shopify_inventory_item_id_index')) {
                $table->index('shopify_inventory_item_id', 'product_variation_shopify_inventory_item_id_index');
            }
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
            $table->dropIndex('products_shopify_product_id_index');
            $table->dropIndex('products_shopify_inventory_item_id_index');
        });

        Schema::table('product_variation', function (Blueprint $table) {
            $table->dropIndex('product_variation_shopify_inventory_item_id_index');
        });
    }

    /**
     * Check if an index exists on a table
     *
     * @param string $table
     * @param string $index
     * @return bool
     */
    private function hasIndex($table, $index)
    {
        $connection = Schema::getConnection();
        $indexes = $connection->getDoctrineSchemaManager()->listTableIndexes($table);
        return array_key_exists($index, $indexes);
    }
}
