<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UpdateMallsharkSelectedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->update(['mallshark_selected' => DB::raw('FLOOR(RAND() * 2)')]);
    }
}
