<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Packages\Core\Models\V3\StateCategoryTax;

class DeleteExpiredTaxRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tax:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired state category tax records from database';

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
        try {
            $now = Carbon::now();
            
            // Find all expired tax records
            $expiredRecords = StateCategoryTax::where('expires_at', '<', $now)->get();

            if ($expiredRecords->isEmpty()) {
                $this->info('✓ No expired tax records found to delete.');
                return 0;
            }

            // Delete expired records
            $deletedCount = StateCategoryTax::where('expires_at', '<', $now)->delete();

            $this->info("✓ Successfully deleted {$deletedCount} expired tax record(s).");
            $this->line("Deleted at: {$now->toDateTimeString()}");

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Error deleting expired tax records: " . $e->getMessage());
            return 1;
        }
    }
}
