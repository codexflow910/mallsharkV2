<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\V2\{States,Store};
use Illuminate\Support\Facades\DB;
use Packages\Shipping\Models\V2\{FreeShipping,ShippingProfile};

class GeneralShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        try {
            $stores = Store::all();
            $stores = Store::whereIn('store_id',['162','227'])->get();
            foreach ($stores as $store) {
                // Skip if store already has a general profile
                $existing = ShippingProfile::where('store_id', $store->store_id)->where('is_general', 1)->first();
                if ($existing) continue;
                $state = States::where('state_id', $store->store_state)->first();
                // Only create if state exists
                if ($state) {
                $profile = ShippingProfile::create([
                    'store_id' => $store->store_id,
                    'name' => 'General Shipping',
                    'address' => $store->street_address,
                    'city' => $store->store_city,
                    'state_id' => $state->state_id,
                    'zip_code' => $store->zip_code,
                    'is_general' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                FreeShipping::create([
                    'profile_type' => 'general',
                    'type_id' => $profile->id,
                    'free_shipping_time' => '1-2 Days', // Default free shipping time
                ]);
                DB::commit();
            echo "✅ General shipping profiles created for all stores.\n";
            }
            
            }

        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}
