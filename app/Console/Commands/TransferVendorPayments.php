<?php

namespace App\Console\Commands;

use App\Repositories\stripe\StripeAccess;
use Illuminate\Console\Command;

class TransferVendorPayments extends Command
{
    private $stripeAccess;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendorPayments:transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer vendor payments after escrow time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->stripeAccess = new StripeAccess();

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        $this->info('Executing vendor payment transfer...');
        $this->stripeAccess->transferVendorPayments();
    }
}
