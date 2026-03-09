<?php

namespace Database\Seeders;

use App\Models\Point;
use Illuminate\Database\Seeder;

class PointsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pointsData = [
            [
                'user_id' => 12,
                'store_id' => 36,
                'amount' => 50,
            ],
            [
                'user_id' => 102,
                'store_id' => 44,
                'amount' => 30,
            ],
            // Add more data as needed...
        ];

        // Insert custom points data
        foreach ($pointsData as $data) {
            Point::create([
                'user_id' => $data['user_id'],
                'store_id' => $data['store_id'],
                'amount' => $data['amount'],
            ]);
        }
    }
}
