<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Product\Models\V2\Label;

class addLabelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            ['title' => 'Free Shipping', 'slug' => 'free_shipping'],
            ['title' => 'Fast Delivery', 'slug' => 'fast_delivery'],
            ['title' => 'Best Sellers', 'slug' => 'best_sellers'],
            ['title' => 'Ship From Usa', 'slug' => 'ship_from_usa'],
        ];

        foreach ($labels as $label) {
            Label::updateOrCreate(
                ['slug' => $label['slug']],
                ['title' => $label['title']]
            );
        }
    }
}
