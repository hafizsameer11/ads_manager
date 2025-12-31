<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Advertiser;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction as PayPalTransaction;
use PayPal\Exception\PayPalConnectionException;
use Illuminate\Support\Str;

class PayPalService
{
    protected $apiContext;

    public function __construct()
    {
        $clientId = Setting::get('paypal_client_id', '');
        $secret = Setting::get('paypal_secret', '');
        $mode = Setting::get('paypal_mode', 'sandbox');

        if ($clientId && $secret) {
            $this->apiContext = new ApiContext(
                new OAuthTokenCredential($clientId, $secret)
            );
            $this->apiContext->setConfig([
                'mode' => $mode,
                'log.LogEnabled' => true,
                'log.FileName' => storage_path('logs/paypal.log'),
                'log.LogLevel' => 'DEBUG',
                'cache.enabled' => true,
                'cache.FileName' => storage_path('logs/paypal.cache'),
            ]);
        }
    }

    /**
     * Create a PayPal payment.
     *
     * @param  Advertiser  $advertiser
     * @param  float  $amount
     * @return array
     * @throws \Exception
     */
    public function createPayment(Advertiser $advertiser, float $amount): array
    {
        // Create a pending transaction first
        $transaction = Transaction::create([
            'transactionable_type' => Advertiser::class,
            'transactionable_id' => $advertiser->id,
            'type' => 'deposit',
            'status' => 'pending',
            'amount' => $amount,
            'transaction_id' => 'TXN-' . strtoupper(Str::random(16)),
            'payment_method' => 'paypal',
            'payment_details' => [
                'created_at' => now()->toDateTimeString(),
            ],
            'notes' => 'PayPal payment pending',
        ]);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amountObj = new Amount();
        $amountObj->setCurrency('USD')
            ->setTotal(number_format($amount, 2, '.', ''));

        $paypalTransaction = new PayPalTransaction();
        $paypalTransaction->setAmount($amountObj)
            ->setDescription('Account Deposit - $' . number_format($amount, 2));

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$paypalTransaction]);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('dashboard.advertiser.paypal.success', ['transaction_id' => $transaction->id]))
            ->setCancelUrl(route('dashboard.advertiser.paypal.cancel', ['transaction_id' => $transaction->id]));

        $payment->setRedirectUrls($redirectUrls);

        try {
            $payment->create($this->apiContext);

            // Update transaction with PayPal payment ID
            $transaction->update([
                'payment_details' => array_merge($transaction->payment_details ?? [], [
                    'paypal_payment_id' => $payment->getId(),
                    'paypal_approval_url' => $payment->getApprovalLink(),
                ]),
            ]);

            return [
                'success' => true,
                'approval_url' => $payment->getApprovalLink(),
                'payment_id' => $payment->getId(),
                'transaction_id' => $transaction->id,
            ];
        } catch (PayPalConnectionException $e) {
            \Log::error('PayPal payment creation failed: ' . $e->getData());
            $transaction->markAsFailed('PayPal payment creation failed: ' . $e->getMessage());
            throw new \Exception('Failed to create PayPal payment: ' . $e->getMessage());
        }
    }

    /**
     * Execute a PayPal payment.
     *
     * @param  string  $paymentId
     * @param  string  $payerId
     * @param  int  $transactionId
     * @return bool
     */
    public function executePayment(string $paymentId, string $payerId, int $transactionId): bool
    {
        $transaction = Transaction::find($transactionId);
        
        if (!$transaction || $transaction->status !== 'pending') {
            return false;
        }

        $payment = Payment::get($paymentId, $this->apiContext);
        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($payerId);

        try {
            $payment->execute($execution, $this->apiContext);
            
            // Mark transaction as completed and update balance
            \DB::transaction(function () use ($transaction) {
                $transaction->markAsCompleted();
                
                $advertiser = $transaction->transactionable;
                if ($advertiser instanceof Advertiser) {
                    $advertiser->increment('balance', $transaction->amount);
                    
                    // Send notification
                    if (app()->bound(\App\Services\NotificationService::class)) {
                        app(\App\Services\NotificationService::class)->create(
                            $advertiser->user,
                            'deposit_completed',
                            'Deposit Completed',
                            "Your PayPal deposit of $" . number_format($transaction->amount, 2) . " has been completed and added to your balance.",
                            ['transaction_id' => $transaction->id, 'amount' => $transaction->amount]
                        );
                    }
                }
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('PayPal payment execution failed: ' . $e->getMessage());
            $transaction->markAsFailed('PayPal payment execution failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify PayPal webhook.
     *
     * @param  array  $payload
     * @return bool
     */
    public function verifyWebhook(array $payload): bool
    {
        // PayPal webhook verification logic
        // This is a simplified version - you may want to implement full verification
        return true;
    }
}



