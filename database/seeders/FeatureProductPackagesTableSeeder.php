<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class FeatureProductPackagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('feature_product_packages')->insert([
            ['package_name' => 'Basic', 'status' => 1, 'days' => 30, 'price' => 0.5, 'description' => 'Introducing our Basic Package, the perfect solution for those just starting out. This package includes all the essential features you need to get started'],
            ['package_name' => 'Standard', 'status' => 1, 'days' => 60, 'price' => 0.5, 'description' => 'Introducing our Basic Package, the perfect solution for those just starting out. This package includes all the essential features you need to get started'],
            ['package_name' => 'Premium', 'status' => 1, 'days' => 90, 'price' => 0.5, 'description' => 'Introducing our Basic Package, the perfect solution for those just starting out. This package includes all the essential features you need to get started'],
        ]);
    }
}
