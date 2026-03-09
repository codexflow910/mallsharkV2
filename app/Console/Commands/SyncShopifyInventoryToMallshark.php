<?php

namespace App\Console\Commands;

use App\Models\ShopifyStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Packages\Product\Models\Product;
use Packages\Import\Contracts\ShopifyHelpers;
use Packages\Product\Models\ProductVariation;

class SyncShopifyInventoryToMallshark extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync inventory of Shopify imported products with Shopify live stock.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 Starting Shopify inventory sync...');

        $products = Product::where('is_shopify_import', 1)->get();
        $stores = ShopifyStore::all();

        foreach ($stores as $store) {
            $shopifyCredentials = (object)[
                'access_token' => $store->access_token,
                'shop_url' => $store->shop_url,
            ];

            foreach ($products as $product) {
                $updated = false;

                // 🔹 First handle product variations
                $variations = ProductVariation::where('product_id', $product->product_id)
                    ->whereNotNull('shopify_inventory_item_id')
                    ->get();

                foreach ($variations as $variation) {
                    $qty = ShopifyHelpers::getAvailableQuantityForInventoryItem($variation->shopify_inventory_item_id, $shopifyCredentials);

                    if (!is_null($qty)) {
                        ProductVariation::where('variation_id', $variation->variation_id)
                            ->update(['current_stock' => $qty]);

                        Log::info("✅ Synced variant {$variation->variation_id} from store {$store->id}, qty: $qty");
                        $updated = true;
                    }
                }

                // 🔹 Sum and update parent product stock from all variations
                if ($updated) {
                    $totalStock = ProductVariation::where('product_id', $product->product_id)->sum('current_stock');
                    Product::where('product_id', $product->product_id)->update(['current_stock' => $totalStock]);
                    // $product->update(['current_stock' => $totalStock]);
                }

                // 🔹 If no variations, handle simple product
                if ($variations->isEmpty() && $product->shopify_inventory_item_id) {
                    $qty = ShopifyHelpers::getAvailableQuantityForInventoryItem($product->shopify_inventory_item_id, $shopifyCredentials);

                    if (!is_null($qty)) {
                        Product::where('product_id', $product->product_id)
                            ->update(['current_stock' => $qty]);

                        Log::info("✅ Synced simple product {$product->product_id} from store {$store->id}, qty: $qty");
                        $updated = true;
                    }
                }

                if (!$updated) {
                    Log::warning("⚠️ Could not sync product {$product->product_id} using store {$store->id}");
                }
            }
        }

        $this->info('✅ Shopify inventory sync complete.');
    }
}
