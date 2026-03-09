<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Packages\Product\Models\V2\Product;
use Packages\Product\Config\ProductConstants;
class ProductsAlgoliaSyncing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:algolia-syncing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $startTime = now();

        try {
            Log::channel('algolia')->info('Algolia syncing started', [
                'command' => $this->signature,
                'started_at' => $startTime->toDateTimeString()
            ]);

            $threshold = Carbon::now()->subMinutes(45);

            Log::channel('algolia')->info('Threshold set', [
                'threshold' => $threshold->toDateTimeString(),
                'minutes_back' => 30
            ]);

            $batch = [];
            $batchSize = 500;
            $totalProcessed = 0;
            $batchCount = 0;

            foreach (Product::where('status', ProductConstants::STATUS_ACTIVE)
                    ->where('algolia_batch', '=', 1)
                    ->with(['store', 'tags', 'aiTags', 'labels', 'categories']) // Eager load relationships
                    ->orderBy('product_id')
                    ->cursor() as $product) {

                $batch[] = $product;
                $totalProcessed++;

                if (count($batch) === $batchSize) {
                    try {
                        collect($batch)->searchable(); // bulk update to Algolia

                        // Reset batch flag ONLY after successful sync
                        $productIds = collect($batch)->pluck('product_id')->toArray();
                        Product::whereIn('product_id', $productIds)->update(['algolia_batch' => 0]);

                        $batchCount++;

                        Log::channel('algolia')->info('Batch synced to Algolia', [
                            'batch_number' => $batchCount,
                            'batch_size' => count($batch),
                            'total_processed' => $totalProcessed,
                            'sample_product_id' => $batch[0]->product_id ?? null,
                            'sample_product_title' => $batch[0]->title ?? null
                        ]);

                        $batch = [];
                    } catch (\Exception $e) {
                        Log::channel('algolia')->error('Error syncing batch to Algolia', [
                            'batch_number' => $batchCount + 1,
                            'batch_size' => count($batch),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                }
            }

            // Sync remaining products in the last batch
            if (!empty($batch)) {
                try {
                    collect($batch)->searchable();

                    // Reset batch flag ONLY after successful sync
                    $productIds = collect($batch)->pluck('product_id')->toArray();
                    Product::whereIn('product_id', $productIds)->update(['algolia_batch' => 0]);

                    $batchCount++;

                    Log::channel('algolia')->info('Final batch synced to Algolia', [
                        'batch_number' => $batchCount,
                        'batch_size' => count($batch),
                        'total_processed' => $totalProcessed,
                        'sample_product_id' => $batch[0]->product_id ?? null,
                        'sample_product_title' => $batch[0]->title ?? null
                    ]);
                } catch (\Exception $e) {
                    Log::channel('algolia')->error('Error syncing final batch to Algolia', [
                        'batch_size' => count($batch),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);

            Log::channel('algolia')->info('Algolia syncing completed successfully', [
                'total_products_processed' => $totalProcessed,
                'total_batches' => $batchCount,
                'batch_size' => $batchSize,
                'duration_seconds' => $duration,
                'started_at' => $startTime->toDateTimeString(),
                'completed_at' => $endTime->toDateTimeString()
            ]);

            return 0;

        } catch (\Exception $e) {
            Log::channel('algolia')->error('Algolia syncing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
