<?php

namespace Database\Seeders;

use App\Models\Compains;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PicVendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vendors = [
            [
                'name' => 'Picvendor',
                'slug' => 'picvendor',
                'description' => 'Description for Vendor',
                'terms' => json_encode(['min_products' => 10]), 
                'benefits' => json_encode(['platform_fee' => false, 'percentage' => '0']), 
                'start_at' => '2023-09-12',
                'end_at' => '2023-10-12',
                'duration' => 60,
                'users' => '',
                'max_users' => 50,
            ],
            
        ];

        DB::table('campaigns')->insert($vendors);
    }
}
