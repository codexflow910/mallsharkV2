<?php

namespace App\Jobs;
use App\Http\Controllers\MailgunController;
use App\Mail\ProductImportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Packages\Import\Http\Controllers\V2\ShopifyController;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ImportProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the unique lock should be maintained.
     *
     * @var int
     */
    public $uniqueFor = 3600; // 1 hour

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutes

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $products;
    protected $userEmail;
    protected $userId;
    protected $shopUrl;
    protected $accessToken;

    public function __construct(string $shopUrl, string $accessToken, array $products, string $userEmail, string $userId)
    {
        $this->shopUrl = $shopUrl;
        $this->accessToken = $accessToken;
        $this->products = $products;
        $this->userId = $userId;
        $this->userEmail = $userEmail;
    }

    /**
     * The unique ID of the job.
     * Prevents duplicate jobs for the same user import.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        // Create unique ID based on user and product IDs being imported
        $productIds = array_map(function($product) {
            return preg_replace('/[^0-9]/', '', $product->product_id ?? $product['product_id'] ?? '');
        }, $this->products);
        sort($productIds);

        return 'import_' . $this->userId . '_' . md5(implode('_', $productIds));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ShopifyController $shopifyController)
    {
        $paymentLog = new Logger('');
        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shopify.log')), Logger::ERROR);
        $paymentLog->info('Import job started', ['total_products' => count($this->products), 'user_id' => $this->userId]);
        $client = $shopifyController->getShopifyClient($this->shopUrl, $this->accessToken);

        $shopifyRepository = app(\Packages\Import\Repositories\Eloquent\V2\ShopifyRepository::class);
        $successCount = 0;
        $failedCount = 0;

        // Process products in chunks of 5 for better memory management
        $chunks = array_chunk($this->products, 5);
        $totalChunks = count($chunks);

        foreach ($chunks as $chunkIndex => $chunk) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shopify.log')), Logger::ERROR);
            $paymentLog->info('Processing chunk', [
                'chunk' => ($chunkIndex + 1),
                'total_chunks' => $totalChunks,
                'chunk_size' => count($chunk)
            ]);

            foreach ($chunk as $product) {
                $productId = preg_replace('/[^0-9]/', '', $product->product_id);

                try {
                    $paymentLog = new Logger('');
                    $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shopify.log')), Logger::ERROR);
                    $paymentLog->info('Importing product', [
                        'product_id' => $productId,
                        'product_title' => $product->title ?? 'N/A'
                    ]);

                    DB::beginTransaction();
                    $shopifyController->processSingleProduct($client, $product, $this->userId);
                    DB::commit();

                    // Mark as complete after successful import
                    $shopifyRepository->markProductImportComplete($productId, $this->userId);
                    $successCount++;

                    $paymentLog = new Logger('');
                    $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shopify.log')), Logger::ERROR);
                    $paymentLog->info('Product imported successfully', [
                        'product_id' => $productId,
                        'progress' => "$successCount/" . count($this->products)
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();

                    // Mark as failed so it can be retried
                    $shopifyRepository->markProductImportFailed($productId, $this->userId);
                    $failedCount++;

                    $paymentLog = new Logger('');
                    $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shopify.log')), Logger::ERROR);
                    $paymentLog->error('Product import failed', [
                        'product_id' => $productId,
                        'product_title' => $product->title ?? 'N/A',
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
            }

            // Free up memory after each chunk
            gc_collect_cycles();
        }

       try {
            $htmlContent = view('mail.shopify.product-import-completed')->render();
            $params = [
                'from'    => config('mail.from.address', 'no-reply@' . config('services.mailgun.domain')),
                'to'      => $this->userEmail,
                'subject' => 'Products are imported to MallShark successfully',
                'html'    => $htmlContent,
            ];
            (new MailgunController())->SendMailgunMail($params);
        } catch (\Exception $e) {
            $paymentLog->error('Failed to send import completion email', [
                'email' => $this->userEmail,
                'error' => $e->getMessage()
            ]);
        }
        $paymentLog = new Logger('');
        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shopify.log')), Logger::ERROR);
        $paymentLog->info('Import completed', [
            'Result' => 'Done',
            'Success' => $successCount,
            'Failed' => $failedCount,
            'Total' => count($this->products),
            'User' => $this->userEmail
        ]);
    }
}
