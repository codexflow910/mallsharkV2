<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Packages\Home\Models\V2\Cookies;
use Packages\Search\Models\V2\RecommendedProduct;
use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Support\Facades\Log;
use Packages\Product\Models\V2\{Attachments,AttachmentSizes,Product,Store,SearchTags};

class RecommondedProducts extends Command
{
    protected $signature = 'recommended:generate';
    protected $description = 'Generate recommended products for a specific user or all users with cookies';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $userId = null;

        if ($userId) {
            $message = "Generating recommendations for user ID: $userId";
            Log::info($message);
            // $this->generateForUser($userId);
        } else {
            $message = "Generating recommendations for users";
            
            $userIds = Cookies::where('updated', true)
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id');

            foreach ($userIds as $id) {
                $this->generateForUser($id);
            }

        }
        $this->info("Recommendation generation complete.");
    }

    private function generateForUser($userId)
    {
        $topKeyword = Cookies::where('user_id', $userId)->where('type', 'keyword')->orderByDesc('frequency')->first();
        $topTag = Cookies::where('user_id', $userId)->where('type', 'tag')->orderByDesc('frequency')->first(); 
        $keywords = $topKeyword ? [$topKeyword->value] : [];
        $tags = $topTag ? [$topTag->value] : [];
        $searchTerms = array_filter(array_merge($keywords, $tags), function ($term) {
            return !empty($term) && $term !== '*';
        });



        $client = SearchClient::create(env('ALGOLIA_APP_ID'), env('ALGOLIA_SECRET'));
        $index = $client->initIndex(env('PRODUCT_INDEX'));
        $recommendedProducts = [];
        $recommendedIds = [];
        foreach ($searchTerms as $term) {
            try {
                $results = $index->search($term, [
                    'hitsPerPage' => 100,
                    'clickAnalytics' => true,
                ]);        
                foreach ($results['hits'] as $product) {
                    if($product['effective_price'] >= 300){
                        continue; // Skip products that are $300 or more
                    }
                    $formatted = $this->formatProduct($product, $results['queryID'], 'recommended');
                    if (!in_array($formatted['product_id'], $recommendedIds)) {
                        $recommendedProducts[] = $formatted;
                        $recommendedIds[] = $formatted['product_id'];
                        if (count($recommendedProducts) >= 100) {
                            break 2;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Search failed for term down '$term': " . $e->getMessage());
            }
        }
        $bestSellerIndex = $client->initIndex(env('PRODUCTS_SALES_ASC'));
        $bestSellerProducts = [];
        $bestSellerIds = [];
        try {
            $bestSellerResults = $bestSellerIndex->search('*', [
                'hitsPerPage' => 100,
                'clickAnalytics' => true,
            ]);
            foreach ($bestSellerResults['hits'] as $product) {
                if($product['effective_price'] >= 300){
                        continue; // Skip products that are $300 or more
                    }
                $formatted = $this->formatProduct($product, $bestSellerResults['queryID'], 'best_seller');
                if (!in_array($formatted['product_id'], $recommendedIds) && !in_array($formatted['product_id'], $bestSellerIds)) {
                    $bestSellerProducts[] = $formatted;
                    $bestSellerIds[] = $formatted['product_id'];
                    if (count($bestSellerProducts) >= 60) {
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Best seller search failed: " . $e->getMessage());
        }
        $allProducts = array_merge($recommendedProducts, $bestSellerProducts);

        RecommendedProduct::updateOrCreate(
            ['user_id' => $userId],
            ['products' => json_encode($allProducts)]
        );
        $tenMinutesAgo = now()->subMinutes(10);
        $revertCookies = Cookies::where('updated', true)->where('updated_at', '<', $tenMinutesAgo)->update(['updated'=>false]);
        // $this->line("Saved " . count($recommendedProducts) . " products (10 recommended + 60 best sellers) for user $userId");
    }

    private function formatProduct($product, $queryId = null, $source = 'recommended')
    {
        return [
            'product_id'          => $product['id'] ?? $product['objectID'],
            'objectID'            => $product['objectID'],
            'title'               => $product['name'] ?? '',
            'store_id'            => $product['storeId'] ?? null,
            'slug'                => $product['slug'] ?? null,
            'price'               => $product['price'] ?? 0,
            'description'         => $product['description'] ?? '',
            'current_stock'       => $product['current_stock'] ?? 0,
            'status'              => $product['status'] ?? '1',
            'product_type'        => $product['product_type'] ?? 'simple',
            'on_sale'             => $product['on_sale'] ?? 0,
            'mallshark_selected'  => $product['mallshark_selected'] ?? 0,
            'sale_price'          => $product['sale_price'] ?? 0,
            'shipping_option_id'  => $product['shipping_option_id'] ?? null,
            'discount_percentage' => $product['discount_percentage'] ?? 0,
            'feature_image'       => $product['feature_image'] ?? null,
            'categories'          => $product['categories'] ?? [],
            'created_at'          => $product['created_at'] ?? now(),
            'query_id'            => $queryId,
            'source'              => $source
        ];
    }
}
