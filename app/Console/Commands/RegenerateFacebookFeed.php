<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Packages\Product\Models\V2\Product;
use Packages\Product\Config\ProductConstants;
use App\Services\FacebookXmlService;

class RegenerateFacebookFeed extends Command
{
    protected $signature = 'facebook:regenerate-feed';
    protected $description = 'Regenerate Facebook XML feed from cached product IDs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            // Get cached product IDs
            $productIds = Cache::get('facebook_feed_product_ids', []);

            if (empty($productIds)) {
                $this->warn('No cached product IDs found. Please generate the feed manually first.');
                Log::warning('Facebook feed regeneration skipped: No cached product IDs');
                return 1;
            }

            $this->info('Found ' . count($productIds) . ' cached product IDs');

            // Fetch active products
            $status = ProductConstants::STATUS_ACTIVE;
            $products = Product::where('status', $status)
                ->whereIn('product_id',$productIds)
                ->get();

            if ($products->isEmpty()) {
                $this->error('No active products found for the cached IDs');
                Log::error('Facebook feed regeneration failed: No active products found');
                return 1;
            }

            $this->info('Found ' . $products->count() . ' active products');

            // Generate XML
            $service = new FacebookXmlService();
            $xmlContent = $service->generateXml($products);

            // Save to file
            $filename = 'facebook_feed.xml';
            $dir = public_path('analytics');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($filePath, $xmlContent);

            $this->info('Facebook feed regenerated successfully!');
            $this->line('File: ' . $filePath);
            Log::info('Facebook feed regenerated successfully', [
                'products_count' => $products->count(),
                'file_path' => $filePath
            ]);

            return 0;

        } catch (\Throwable $e) {
            $this->error('Failed to regenerate Facebook feed: ' . $e->getMessage());
            Log::error('Facebook feed regeneration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
