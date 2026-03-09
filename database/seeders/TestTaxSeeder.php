<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Core\Models\V2\StateCategoryTax;
use Packages\Store\Models\V2\States;
use Packages\Core\Models\V2\Category;
use Packages\Core\Models\V2\TaxCategory;
use Illuminate\Support\Facades\Storage;

class TestTaxSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting Test Tax Seeder...');

        // Your provided categories
        $categoryMap = [
            95 => 'Accessories & Supplies',
            97 => 'Office Electronics',
            96 => 'Headphones',
            98 => 'Anklets',
            99 => 'Bracelets',
            101 => 'Fashion Hoodies & Sweatshirts',
            102 => 'Suits & Sport Coats',
            104 => 'Fashion Sneakers',
            108 => 'Cat Supplies',
            116 => "Children's Books",
        ];
  // Exempt categories
        $exemptCategories = [95, 108, 116];
        $exemptStates = ['AL','NY', 'CA'];
        $states = States::all()->where('country_id', 1);

        if ($states->isEmpty()) {
            $this->command->warn('⚠️ No states found.');
            return;
        }
  // Prepare CSV export
        $exportPath = 'exports/state_category_taxes.csv';
        Storage::makeDirectory('exports');
        $csvFile = fopen(storage_path('app/' . $exportPath), 'w');
        fputcsv($csvFile, ['State Name', 'Mallshark Category Name', 'Rate']);

        foreach ($states as $state) {
            foreach ($categoryMap as $id => $name) {
                // Create/find tax category for each
                $taxCategory = TaxCategory::firstOrCreate(
                    ['stripe_tax_id' => 'TEST_' . strtoupper(str_replace(' ', '_', $name))],
                    [
                        'name' => $name,
                    ]
                );
                // Apply exemption rule
                if (in_array($id, $exemptCategories) && in_array(strtoupper($state->code), $exemptStates)) {
                    $rate = 0.0000; // Exempted
                } else {
                    $rate = round(mt_rand(30, 100) / 1000, 4); // 3% - 10%
                }
                // Assign a random tax rate between 3% and 10%
                $rate = round(mt_rand(30, 100) / 1000, 4);

                StateCategoryTax::updateOrCreate(
                    [
                        'state_id' => $state->state_id,
                        'mallshark_categories' => $id,
                    ],
                    [
                        'tax_category_id' => $taxCategory->id,
                        'rate' => $rate,
                    ]
                );
                  // Write to CSV
                fputcsv($csvFile, [
                    $state->state_name,
                    $name,
                    $rate,
                ]);
            }
        }
        fclose($csvFile);
        $this->command->info('✅ Test tax data seeded successfully for all states and 10 categories!');
        $this->command->info('📁 CSV file generated at: ' . storage_path('app/' . $exportPath));
    }
}
