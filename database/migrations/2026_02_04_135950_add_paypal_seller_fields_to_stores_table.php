<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaypalSellerFieldsToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // PayPal merchant ID from Partner Referrals API
            $table->string('paypal_merchant_id')->nullable()->after('account_link');

            // Tracking ID used during partner referral creation
            $table->string('paypal_tracking_id')->nullable()->after('paypal_merchant_id');

            // Connection status: 0 = not connected, 1 = connected
            $table->enum('paypal_connected', ['0', '1'])->default('0')->after('paypal_tracking_id');

            // PayPal account status (e.g., ACTIVE, PENDING, etc.)
            $table->string('paypal_account_status')->nullable()->after('paypal_connected');

            // Permissions granted by seller (stored as JSON)
            $table->json('paypal_permissions_granted')->nullable()->after('paypal_account_status');

            // The onboarding signup URL
            $table->text('paypal_onboarding_link')->nullable()->after('paypal_permissions_granted');

            // Whether payments are receivable (email confirmed, etc.)
            $table->boolean('paypal_payments_receivable')->default(false)->after('paypal_onboarding_link');

            // Primary email confirmed status
            $table->boolean('paypal_primary_email_confirmed')->default(false)->after('paypal_payments_receivable');

            // Timestamp of last status check
            $table->timestamp('paypal_status_checked_at')->nullable()->after('paypal_primary_email_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_merchant_id',
                'paypal_tracking_id',
                'paypal_connected',
                'paypal_account_status',
                'paypal_permissions_granted',
                'paypal_onboarding_link',
                'paypal_payments_receivable',
                'paypal_primary_email_confirmed',
                'paypal_status_checked_at',
            ]);
        });
    }
}
