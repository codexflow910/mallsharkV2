<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Core\Models\V2\BaseRate;
use Packages\Core\Models\V2\TaxCategory;
use Packages\Core\Models\V2\StateCategoryTax;
use Packages\Store\Models\V2\States;
use Packages\Core\Models\V2\Category;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * ===============================
         * Base State Tax Rates
         * ===============================
         */
        $stateRates = [
            'CA' => 0.0725,
            'TX' => 0.0625,
            'NY' => 0.04,
            'FL' => 0.06,
        ];

        foreach ($stateRates as $code => $rate) {
            $state = States::where('code', $code)->first();
            if ($state) {
                BaseRate::updateOrCreate(
                    ['state_id' => $state->state_id],
                    ['base_rate' => $rate]
                );
            }
        }

        /**
         * ===============================
         * Fetch only child categories
         * ===============================
         */
        $categories = Category::whereNotNull('parentCategoryID')
            ->where('parentCategoryID', '!=', 0)
            ->get();
        if ($categories->isEmpty()) {
            $this->command->warn('⚠️ No child categories found (categories with parentCategoryID).');
            return;
        }

        /**
         * ===============================
         * Category-State Tax Mapping with Stripe Tax Categories
         * ===============================
         */
        foreach ($stateRates as $code => $baseRate) {
            $state = States::where('code', $code)->first();
            if (!$state) continue;

            foreach ($categories as $category) {
                $categoryName = strtoupper($category->name ?? $category->slug ?? '');

                // 🧮 Adjust modifier based on category name
                $modifier = match (true) {
                    str_contains($categoryName, 'FOOD') => -0.02,
                    str_contains($categoryName, 'BOOK') => -0.015,
                    str_contains($categoryName, 'ELECTRONICS') => +0.02,
                    str_contains($categoryName, 'CLOTHING') => -0.01,
                    str_contains($categoryName, 'JEWELRY') => +0.03,
                    str_contains($categoryName, 'SMART') => +0.015,
                    str_contains($categoryName, 'AUTOMOTIVE') => +0.01,
                    default => 0
                };

                $finalRate = max(0, round($baseRate + $modifier, 4));

                /**
                 * ===============================
                 * Match Stripe Tax Category by name or description
                 * ===============================
                 */
                $taxCategory = TaxCategory::where('name', 'LIKE', '%' . $category->name . '%')
                    ->first();

                // If no match found, fall back to a default
                if (!$taxCategory) {
                    $taxCategory = TaxCategory::firstOrCreate(
                        ['stripe_tax_id' => 'fallback_' . $categoryName],
                        [
                            'name' => ucwords(strtolower(str_replace('_', ' ', $categoryName))),
                            'type' => 'auto-fallback',
                            'description' => 'Auto-matched fallback category.',
                        ]
                    );
                }

                /**
                 * ===============================
                 * Create or Update StateCategoryTax link
                 * ===============================
                 */
                StateCategoryTax::updateOrCreate(
                    [
                        'state_id' => $state->state_id,
                        'mallshark_categories' => $category->id,
                    ],
                    [
                        'tax_category_id' => $taxCategory->id,
                        'rate' => $finalRate,
                    ]
                );
            }
        }

        $this->command->info('✅ Stripe tax categories linked successfully with MallShark categories!');
    }
}
