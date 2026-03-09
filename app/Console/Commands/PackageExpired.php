<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Packages\Core\Models\V2\MallsharkPurchasedPackage;

class PackageExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update packages to expired if they are past their expiry date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->startOfDay();
         $expiredPackages = MallsharkPurchasedPackage::
            where('expired_at', '<', $today)
            ->get();

        if ($expiredPackages->isEmpty()) {
            $this->info('No expired packages found.');
            return;
        }

        foreach ($expiredPackages as $package) {
            $package->update([
                'status' => 'expired',
                'updated_at' => now()
            ]);

            $this->info("Package ID {$package->id} marked as expired.");
        }

        $this->info('All expired packages have been updated.');
    }
}
