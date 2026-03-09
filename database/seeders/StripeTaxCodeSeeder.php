<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;



class StripeTaxCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('tax_categories')->truncate();
        $now = Carbon::now();
        $categories = [
            // Clothing & Shoes
            ['name' => 'clothing', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'womens-clothing', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'mens-clothing', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'girls-clothing', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'boys-clothing', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'baby-clothing', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'shoes', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'womens-shoes', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'mens-shoes', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'girls-shoes', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'boys-shoes', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'baby-shoes', 'stripe_tax_id' => 'txcd_30011000', 'created_at' => $now, 'updated_at' => $now],

            // Jewelry, Watches, Accessories
            ['name' => 'jewelry-and-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'womens-jewelry', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'girls-jewelry', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'womens-watches', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'mens-watches', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'watches', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now], // assuming watches is for all

            // Food, Digital, Books
            ['name' => 'food', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'digital-products', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'books', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],

            // Health & Medical
            ['name' => 'health-care', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'medical-supplies-equipment', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'baby-and-child-care', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'vision-care', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'vitamins-and-dietary-supplements', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'sports-nutrition', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],

            // --- General Tangible Goods (txcd_99999999) ---
            ['name' => 'accessories-supplies', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'uncategorized', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'home-and-garden', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pet-supplies', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'art', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'beauty', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'electronics', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'handmade', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'kitchen-and-dining', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'dog-supplies', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cat-supplies', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'fish-and-aquatics', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'makeup', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'computers', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'computer-accessories-peripherals', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'external-components', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'printers', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'scanners', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'smart-home-2', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'automotive', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'playtime-&-activities', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'womens-fashion', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'mens-fashion', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'health-and-household', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'sports-and-outdoors', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'bedding', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'bath', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'furniture', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now], // Overridden later with furniture code
            ['name' => 'home-decor', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'irons-steamers', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'vacuums-floor-care', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'storage-and-organization', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cleaning-supplies', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'small-animal-supplies', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'garden-and-outdoor', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'car-care', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'car-electronics-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'exterior-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'interior-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lights-lighting-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'motorcycle-powersports', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'oils-fluids', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'paint-paints-supplies', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'performance-parts-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'replacement-parts', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'rv-parts-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'tires-wheels', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'tools-equipment', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'automotive-enthusiast-merchandise', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'heavy-duty-commercial-vehicle-equipment', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'toys-&-games', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'womens-handbags', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'mens-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'oral-care', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'skincare', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'sports-deals', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'home-improvement', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'fashion-for-all', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'girls-fashion', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'boys-fashion', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'baby-fashion', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'sales-deals', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'girls-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'boys-accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'accessories', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now], // general accessories
            ['name' => 'baby-accessor', 'stripe_tax_id' => 'txcd_99999999', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('tax_categories')->insert($categories);
    }
    
}
