<?php

namespace App\Http\Controllers;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use Illuminate\Http\Request;
use Exception;
use Log;
class PayPalPaymentController extends Controller
{
    private $apiContext;

    public function __construct()
    {
        // Use the .env variables directly for PayPal configuration
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID'),    // Fetch PayPal Client ID from .env
                env('PAYPAL_SECRET')        // Fetch PayPal Secret from .env
            )
        );

        $this->apiContext->setConfig([
            'mode' => env('PAYPAL_MODE', 'sandbox'),    // Sandbox or live mode
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'ERROR',                 // Log level can be DEBUG, INFO, WARNING, ERROR
        ]);
    }

    // Create the payment
    public function createPayment()
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal('10.00'); // A smaller amount to see if the issue persists
        $amount->setCurrency('USD');


        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription('Payment for your order');
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('paypal.success'))
            ->setCancelUrl(route('paypal.cancel'));

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);
        try {
            $payment->create($this->apiContext);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            Log::error('PayPal Connection Error: ' . $ex->getData());
            return back()->withError('Something went wrong with PayPal');
        } catch (Exception $e) {
            Log::error('General Error: ' . $e->getMessage());
            return back()->withError('Something went wrong with PayPal');
        }
        return redirect($payment->getApprovalLink());
    }

    // Handle success after user confirms payment on PayPal
    public function success(Request $request)
    {
        $paymentId = $request->input('paymentId');
        $payment = Payment::get($paymentId, $this->apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($request->input('PayerID'));

        try {
            $result = $payment->execute($execution, $this->apiContext);
            return redirect()->route('home')->withSuccess('Payment successful!');
        } catch (Exception $e) {
            return redirect()->route('home')->withError('Payment failed!');
        }
    }

    // Handle cancellation
    public function cancel()
    {
        return redirect()->route('home')->withError('Payment cancelled!');
    }
}