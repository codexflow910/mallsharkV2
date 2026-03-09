<?php

namespace App\Console\Commands;

use App\Http\Controllers\MailgunController;
use Illuminate\Console\Command;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\V2\Product;
use Packages\Store\Config\StoreConstants;
use Packages\Core\Contracts\V2\CoreHelpers;

class SendLowStockNotifications extends Command
{
    use CoreHelpers;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:low-stock-notifications';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Send low stock notifications to store owners';

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
        $lowStockProducts = Product::with(['store.user', 'variations'])
            ->where('status', ProductConstants::STATUS_ACTIVE)
            ->whereHas('store', function ($q) {
                $q->where('status', StoreConstants::STATUS_APPROVED);
            })
            ->where(function ($q) {
                // Case 1: Simple products
                $q->where(function ($sub) {
                    $sub->whereDoesntHave('variations')
                        ->where(function ($inner) {
                            $inner->where('current_stock', '<=', 30)
                                ->whereRaw('current_stock % 5 = 0');
                        });
                });

                // Case 2: Variation products
                $q->orWhereHas('variations', function ($sub) {
                    $sub->where(function ($inner) {
                        $inner->where('current_stock', '<=', 30)
                            ->whereRaw('current_stock % 5 = 0');
                    });
                });
            })
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('No low stock products found today.');
            return 0;
        }

        // return $lowStockProducts;
        // 2. Group products by store
        $groupedByStore = [];

        foreach ($lowStockProducts as $product) {
            $storeId = $product->store_id;

            if (!isset($groupedByStore[$storeId])) {
                $groupedByStore[$storeId] = [
                    'store'  => $product->store,
                    'owner'  => $product->store->user,
                    'items'  => [],
                ];
            }

            if ($product->variations->isEmpty()) {
                // Simple product
                $groupedByStore[$storeId]['items'][] = [
                    'title'         => $product->title,
                    'slug'          => $product->slug,
                    'current_stock' => $product->current_stock,
                    'image'         => $this->getAttachmentUrlById($product->feature_image, 'media'),
                    'variation'     => null,
                ];
            } else {
                // Variations
                foreach ($product->variations as $variation) {
                    if ($variation->current_stock <= 30 && $variation->current_stock % 5 === 0) {
                        $groupedByStore[$storeId]['items'][] = [
                            'title'         => $product->title,
                            'slug'          => $product->slug,
                            'current_stock' => $variation->current_stock,
                            'image'         => $this->getAttachmentUrlById($variation->product_variation_image, 'media'),
                            'variation'     => [
                                'attribute1' => $variation->attribute1,
                                'option1'    => $variation->option1,
                                'attribute2' => $variation->attribute2,
                                'option2'    => $variation->option2,
                                'attribute3' => $variation->attribute3,
                                'option3'    => $variation->option3,
                            ],
                        ];
                    }
                }
            }
        }

        // 3. Send emails store by store
        foreach ($groupedByStore as $storeId => $data) {
            $store      = $data['store'];
            $storeOwner = $data['owner'];
            $items      = $data['items'];

            // Decide recipient(s)
            $overrideEmails = [
                'daimch89@gmail.com',
                'rehmatullah@mindblazetech.com',
                'daimch89+2@gmail.com',
                'neseror884@agiuse.com',
                'talhaarshad427@gmail.com',
                'xebifif292@bankrau.com',
                'wokiwo4241@macho3.com',
                'asifjavaid@mindblazetech.com',
                'chad@mallshark.com',
                'ejaz@mindblazetech.com',
            ];

            if (in_array($storeOwner->email, $overrideEmails)) {
                $recipients = ['chadfugate85@gmail.com', 'care@mallshark.com'];
            } else {
                $recipients = [$storeOwner->email];
            }

            // Render email HTML
            $htmlContent = view('mail.low-stock-notification-daily', [
                'store'      => $store,
                'products'   => $items,
                'storeOwner' => $storeOwner,
            ])->render();

            // Send via Mailgun
            $params = [
                'from'    => env('CARE_EMAIL'),
                'to'      => implode(',', $recipients),
                'subject' => '⚠️ Low Stock Alert – ' . $store->store_name . ' – Needs Restocking',
                'html'    => $htmlContent,
            ];

            $mailgun = new MailgunController();
            $res = $mailgun->SendMailgunMail($params);
            $this->info("Low stock email sent for store: {$store->store_name}");
        }

        return 0;
    }

}
