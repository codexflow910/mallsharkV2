<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RolesAndPermissionsSeeder;
use TikTokVideosSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DiscountSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(TikTokVideosSeeder::class);
        $this->call(FixNullCountryIdInShippingProfilesSeeder::class);
        // \App\Models\User::factory(10)->create();

    }
}
