<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Discount;
use App\Models\DiscountApplicable;
use App\Models\DiscountUsage;
use App\Models\Product;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables if needed (uncomment if necessary)
        DiscountApplicable::truncate();
        DiscountUsage::truncate();
        Discount::truncate();

        // Create sale type discounts without codes
        // Create sale type discount without code
        $saleDiscount = Discount::create([
            'store_id' => 13,
            'title' => 'Summer Sale',
            'type' => 'sale',
            'platform_wide' => false,
            'discount_value_type' => 'fixed',
            'discount_value' => 20,
            'minimum_purchase_amount' => 10,
            'minimum_quantity_items' => 2,
            'max_usage_limit' => 3,
            'one_use_per_customer' => true,
            'start_time' => now(),
            'end_time' => now()->addMonth(),
        ]);
        $saleDiscount = Discount::create([
            'store_id' => 13,
            'title' => 'Winter Sale',
            'type' => 'sale',
            'platform_wide' => false,
            'discount_value_type' => 'percent',
            'discount_value' => 10,
            'minimum_purchase_amount' => 0,
            'minimum_quantity_items' => 1,
            'max_usage_limit' => 5,
            'one_use_per_customer' => false,
            'start_time' => now(),
            'end_time' => now()->addMonth(),
        ]);

        // Create coupon type discount with code
        $couponDiscount = Discount::create([
            'store_id' => 13,
            'code' => 'SAVE10',
            'title' => '10% Off Coupon',
            'type' => 'coupon',
            'platform_wide' => false,
            'discount_value_type' => 'fixed',
            'discount_value' => 10,
            'minimum_purchase_amount' => 100,
            'minimum_quantity_items' => 2,
            'max_usage_limit' => 50,
            'one_use_per_customer' => true,
            'start_time' => now(),
            'end_time' => now()->addMonth(),
        ]);

        // Adding applicable categories for sale discount
        // DiscountApplicable::create([
        //     'discount_id' => $saleDiscount->id,
        //     'applicable_id' => 1, // Example category ID
        //     'applicable_type' => 'App\Models\Category',
        //     'excluded' => false,
        // ]);

        // // Adding applicable products for coupon discount
        // DiscountApplicable::create([
        //     'discount_id' => $couponDiscount->id,
        //     'applicable_id' => 1, // Example product ID
        //     'applicable_type' => 'App\Models\Product',
        //     'excluded' => false,
        // ]);

        // // Adding excluded categories for coupon discount
        // DiscountApplicable::create([
        //     'discount_id' => $couponDiscount->id,
        //     'applicable_id' => 2, // Example category ID
        //     'applicable_type' => 'App\Models\Category',
        //     'excluded' => true,
        // ]);

        // Adding excluded products for sale discount
        DiscountApplicable::create([
            'discount_id' => $saleDiscount->id,
            'applicable_id' => 317, // Example product ID
            'applicable_type' => 'App\Models\Product',
            'excluded' => true,
        ]);

        // Reset foreign key checks back to 1
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
