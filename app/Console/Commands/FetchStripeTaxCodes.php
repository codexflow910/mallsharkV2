<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\TaxCode;
use Packages\Core\Models\V2\TaxCategory; // create this model if needed

class FetchStripeTaxCodes extends Command
{ 
    protected $signature = 'stripe:fetch-taxcodes';
    protected $description = 'Fetch all Stripe tax codes and save them in the database';

    public function handle()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $this->info("Fetching tax codes from Stripe...");

        $taxCodes = TaxCode::all(['limit' => 600]); 

        foreach ($taxCodes->data as $taxCode) {
            TaxCategory::updateOrCreate(
                ['stripe_tax_id' => $taxCode->id],
                [
                    'name' => $taxCode->name,
                ]
            );
        }

        $this->info(count($taxCodes->data) . " tax codes fetched and saved successfully!");
    }
}
