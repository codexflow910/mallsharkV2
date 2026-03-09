<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MailgunController;
use Packages\Distributors\Config\DistributorsConstants;
use Packages\Distributors\Contracts\V3\DistributorHelpers;
use Packages\Distributors\Models\V3\DistributorDocument;

class DistributorExpiryNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'distributor:license-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify sellers about distributor licenses expiring in 15 days and Mark expired approved distributor licenses as pending .';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $expiredCount = DistributorDocument::where('status', DistributorsConstants::APPROVED)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today)
            ->update(['status' => DistributorsConstants::PENDING]);

        $this->info("Expired distributor licenses moved to pending: {$expiredCount}");

        $notifyDays = [15, 7, 1];

        foreach ($notifyDays as $daysBefore) {
            $targetDate = $today->copy()->addDays($daysBefore);

            // Get distributor documents expiring in exactly X days
            $expiringDocuments = DistributorDocument::whereDate('expiry_date', '=', $targetDate)
                ->where('status', 'approved')
                ->get();

            if ($expiringDocuments->isEmpty()) {
                $this->info("No distributor licenses expiring in {$daysBefore} days.");
                continue;
            }

            foreach ($expiringDocuments as $document) {
                try {
                    $storeId = $document->distributor_id ?? null;
                    if (!$storeId) {
                        continue;
                    }

                    $user = DistributorHelpers::getUserByStoreId($storeId);
                    if (!$user || empty($user->email)) {
                        continue;
                    }

                    $sellerId = $user->user_id;
                    $sellerName = $user->name ?? 'Seller';
                    $email = $user->email;
                    $expiryDate = Carbon::parse($document->expiry_date)->format('M d, Y');
                    $brandName = $document->distributor_name ?? 'Your Distributor';

                    // --- 1️⃣ In-app Notification ---
                    $subject = "Your distributor authorization document will expire in {$daysBefore} day" . ($daysBefore > 1 ? 's' : '') . " (on {$expiryDate}).";
                    DistributorHelpers::pushNotification($sellerId, $subject, 'seller');

                    // --- 2️⃣ Email Notification ---
                    $from = env('CARE_EMAIL');
                    $htmlContent = view('mail.distributor-expiry-reminder', compact('sellerName', 'expiryDate', 'brandName'))->render();

                    $params = [
                        'from'    => $from,
                        'to'      => $email,
                        'subject' => "Distributor License Expiry Reminder ({$daysBefore} days left)",
                        'html'    => $htmlContent,
                    ];

                    (new MailgunController())->SendMailgunMail($params);

                    $this->info("Expiry reminder ({$daysBefore} days left) sent to seller ID {$sellerId} for document ID {$document->id}");
                } catch (\Exception $e) {
                    Log::error("Error sending expiry reminder for document {$document->id}: " . $e->getMessage());
                    $this->error("Failed for document ID {$document->id}");
                }
            }
        }

        $this->info('All distributor expiry reminders processed successfully.');
        return 0;
    }

}
