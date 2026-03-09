<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Packages\Discount\Models\Discount;
use Packages\Discount\Services\V2\DiscountService;

class UpdateDiscountStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update discount statuses based on start and end times';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $discountsService;
    protected $discountService;
    public function __construct(DiscountService $discountService)
    {
        parent::__construct();
        $this->discountService = $discountService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $activeDiscounts = Discount::where('status', 'active')
        ->where('state', 'live')
        ->where(function($q) use ($now) {
            $q->where('end_time', '<', $now)
            ->whereNotNull('end_time')
            ->where('end_time', '!=', '0000-00-00 00:00:00')
            ->where('end_time', '!=', '');
        })
        ->get();
        if ($activeDiscounts){
            foreach ($activeDiscounts as $discount) {
                $discount->status = 'inActive';
                $discount->state = 'expired';
                $discount->save();
                $this->discountService->resetProductPrices($discount->store_id, $discount->id);
            }
        }

        $inactiveDiscounts = Discount::where('status', 'active')->where('state', 'scheduled')
            ->where('start_time', '<=', $now)
            ->where(function($q) use ($now) {
                $q->where('end_time', '>=', $now)
                ->orWhereNull('end_time')
                ->orWhere('end_time', '0000-00-00 00:00:00')
                ->orWhere('end_time', '');
            })
            ->get();

        if ($inactiveDiscounts) {
            foreach ($inactiveDiscounts as $discount) {
                $discount->status = 'active';
                $discount->state = 'live';
                $discount->save();

                if ($discount->type == 'sale') {
                    $this->discountService->updateProductPrices($discount->store_id, $discount);
                }
            }
        }
        $this->info('Discount statuses updated successfully.');
    }
}
 