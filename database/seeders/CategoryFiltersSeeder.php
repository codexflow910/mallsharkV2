<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Product\Models\V3\CategoryFilter;
use Packages\Product\Models\V3\CategoryFilterValue;
use Illuminate\Support\Facades\DB;

class CategoryFiltersSeeder extends Seeder
{
    public function run()
    {
        // Category slug => DB id mapping
        $categories = DB::table('categories')
            ->whereIn('category_slug', [
                'clothing',           // Clothing
                'home-and-garden',    // Discover Home
                'pet-supplies',       // All Pets
                'electronics',        // Electronics
                'computers',          // Computers
                'computer-accessories-peripherals', // Computer Accessories
                'automotive',         // Automotive
                'playtime-&-activities', // Playtime & Activities
                'womens-fashion',     // Women
                'mens-fashion',       // Men
                'health-and-household', // All Health & Household
            ])
            ->pluck('id', 'category_slug')
            ->toArray();

        // Shared filter values
        $materialValues = ['Cotton', 'Polyester', 'Leather', 'Metal', 'Plastic', 'Wood', 'Glass', 'Rubber', 'Silk', 'Wool', 'Nylon', 'Ceramic'];
        $sizeValues = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
        $seasonValues = ['Spring', 'Summer', 'Fall', 'Winter', 'All Season'];
        $genderValues = ['Men', 'Women', 'Unisex', 'Kids'];
        $styleValues = ['Casual', 'Formal', 'Sporty', 'Bohemian', 'Classic', 'Modern', 'Vintage'];
        $occasionValues = ['All', 'Office', 'Party', 'Casual', 'Night Out'];
        $warrantyValues = ['No Warranty', '6 Months', '1 Year', '2 Years', '3 Years', 'Lifetime'];
        $colorValues = ['Red', 'Blue', 'Black', 'White', 'Green', 'Yellow', 'Pink', 'Purple', 'Orange', 'Brown', 'Grey', 'Navy', 'Multicolour'];

        // Clothing filters (also used by Women and Men)
        $clothingFilters = [
            ['key' => 'color', 'name' => 'Color', 'type' => 'multi_select', 'values' => $colorValues],
            ['key' => 'style', 'name' => 'Style', 'type' => 'multi_select', 'values' => $styleValues],
            ['key' => 'size', 'name' => 'Size', 'type' => 'multi_select', 'values' => $sizeValues],
            ['key' => 'material', 'name' => 'Material', 'type' => 'multi_select', 'values' => $materialValues],
            ['key' => 'occasion', 'name' => 'Occasion', 'type' => 'multi_select', 'values' => $occasionValues],
            ['key' => 'season', 'name' => 'Season', 'type' => 'multi_select', 'values' => $seasonValues],
            ['key' => 'gender', 'name' => 'Gender', 'type' => 'single_select', 'values' => $genderValues],
        ];

        // Category => Filters mapping
        $categoryFilters = [
            // Clothing
            'clothing' => $clothingFilters,

            // Discover Home
            'home-and-garden' => [
                ['key' => 'color', 'name' => 'Color', 'type' => 'multi_select', 'values' => $colorValues],
                ['key' => 'material', 'name' => 'Material', 'type' => 'multi_select', 'values' => $materialValues],
                ['key' => 'season', 'name' => 'Season', 'type' => 'multi_select', 'values' => $seasonValues],
                ['key' => 'style', 'name' => 'Style', 'type' => 'multi_select', 'values' => $styleValues],
            ],

            // All Pets
            'pet-supplies' => [
                ['key' => 'color', 'name' => 'Color', 'type' => 'multi_select', 'values' => $colorValues],
                ['key' => 'material', 'name' => 'Material', 'type' => 'multi_select', 'values' => $materialValues],
            ],

            // Electronics
            'electronics' => [
                ['key' => 'color', 'name' => 'Color', 'type' => 'multi_select', 'values' => $colorValues],
                ['key' => 'material', 'name' => 'Material', 'type' => 'multi_select', 'values' => $materialValues],
                ['key' => 'warranty', 'name' => 'Warranty', 'type' => 'single_select', 'values' => $warrantyValues],
            ],

            // Computers
            'computers' => [
                ['key' => 'type', 'name' => 'Type', 'type' => 'multi_select', 'values' => ['Desktop', 'Laptop', 'Monitor', 'Accessories']],
                ['key' => 'processor', 'name' => 'Processor', 'type' => 'multi_select', 'values' => ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'Intel Core i9', 'AMD Ryzen 3', 'AMD Ryzen 5', 'AMD Ryzen 7', 'AMD Ryzen 9', 'Apple M1', 'Apple M2', 'Apple M3']],
                ['key' => 'ram', 'name' => 'RAM', 'type' => 'multi_select', 'values' => ['4GB', '8GB', '16GB', '32GB', '64GB']],
                ['key' => 'storage', 'name' => 'Storage', 'type' => 'multi_select', 'values' => ['128GB', '256GB', '512GB', '1TB', '2TB']],
                ['key' => 'screen_size', 'name' => 'Screen Size', 'type' => 'multi_select', 'values' => ['11"', '13"', '14"', '15.6"', '17"', '21"', '24"', '27"', '32"']],
                ['key' => 'operating_system', 'name' => 'Operating System', 'type' => 'multi_select', 'values' => ['Windows', 'macOS', 'Linux', 'ChromeOS']],
                ['key' => 'touchscreen', 'name' => 'Touchscreen', 'type' => 'boolean', 'values' => ['Yes', 'No']],
                ['key' => 'warranty', 'name' => 'Warranty', 'type' => 'single_select', 'values' => $warrantyValues],
            ],

            // Computer Accessories
            'computer-accessories-peripherals' => [
                ['key' => 'color', 'name' => 'Color', 'type' => 'multi_select', 'values' => $colorValues],
                ['key' => 'material', 'name' => 'Material', 'type' => 'multi_select', 'values' => $materialValues],
            ],

            // Automotive
            'automotive' => [
                ['key' => 'color', 'name' => 'Color', 'type' => 'multi_select', 'values' => $colorValues],
                ['key' => 'material', 'name' => 'Material', 'type' => 'multi_select', 'values' => $materialValues],
                ['key' => 'warranty', 'name' => 'Warranty', 'type' => 'single_select', 'values' => $warrantyValues],
            ],

            // Playtime & Activities
            'playtime-&-activities' => [
                ['key' => 'color', 'name' => 'Color', 'type' => 'multi_select', 'values' => $colorValues],
                ['key' => 'age_range', 'name' => 'Age Range', 'type' => 'multi_select', 'values' => ['0-2', '3-5', '6-8', '9-12', '13+']],
                ['key' => 'type', 'name' => 'Type', 'type' => 'multi_select', 'values' => ['Indoor', 'Outdoor']],
                ['key' => 'material', 'name' => 'Material', 'type' => 'multi_select', 'values' => $materialValues],
            ],

            // Women (same as clothing)
            'womens-fashion' => $clothingFilters,

            // Men (same as clothing)
            'mens-fashion' => $clothingFilters,

            // All Health & Household
            'health-and-household' => [
                ['key' => 'type', 'name' => 'Type', 'type' => 'multi_select', 'values' => ['Supplements', 'Cleaning', 'Medical Supplies', 'Others']],
                ['key' => 'disposable_reusable', 'name' => 'Disposable / Reusable', 'type' => 'single_select', 'values' => ['Disposable', 'Reusable']],
            ],
        ];

        // Seed all filters
        foreach ($categoryFilters as $slug => $filters) {
            if (!isset($categories[$slug])) {
                $this->command->warn("Category slug '{$slug}' not found, skipping.");
                continue;
            }

            $categoryId = $categories[$slug];

            foreach ($filters as $sortOrder => $filter) {
                $categoryFilter = CategoryFilter::updateOrCreate(
                    [
                        'category_id' => $categoryId,
                        'filter_key' => $filter['key'],
                    ],
                    [
                    'filter_name' => $filter['name'],
                    'filter_type' => $filter['type'],
                    'sort_order' => $sortOrder + 1,
                    'is_active' => true,
                    ]
                );

                foreach ($filter['values'] as $valueSortOrder => $value) {
                    CategoryFilterValue::updateOrCreate(
                        [
                            'category_filter_id' => $categoryFilter->id,
                            'value' => $value,
                        ],
                        [
                        'label' => $value,
                        'sort_order' => $valueSortOrder + 1,
                        'is_active' => true,
                        ]
                    );
                }
            }

            $this->command->info("Seeded filters for: {$slug}");
        }
    }
}
