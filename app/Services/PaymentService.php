<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Advertiser;
use App\Models\Setting;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Process deposit for advertiser (creates pending deposit request).
     *
     * @param  Advertiser  $advertiser
     * @param  float  $amount
     * @param  string  $paymentMethod
     * @param  array  $paymentDetails
     * @param  string|null  $screenshotPath
     * @return Transaction
     */
    public function processDeposit(Advertiser $advertiser, float $amount, string $paymentMethod, array $paymentDetails = [], ?string $screenshotPath = null): Transaction
    {
        // Generate transaction ID if not provided
        $transactionId = ($paymentDetails['transaction_id'] ?? null) ?: ('TXN-' . strtoupper(Str::random(16)));

        // Extract notes if provided
        $notes = $paymentDetails['notes'] ?? 'Deposit request pending admin approval';
        unset($paymentDetails['transaction_id'], $paymentDetails['notes']);

        // Create pending transaction (balance NOT updated until admin approval)
        $transaction = Transaction::create([
            'transactionable_type' => Advertiser::class,
            'transactionable_id' => $advertiser->id,
            'type' => 'deposit',
            'status' => 'pending', // Pending until admin approval
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'payment_method' => $paymentMethod,
            'payment_details' => array_merge($paymentDetails, [
                'requested_at' => now()->toDateTimeString(),
            ]),
            'payment_screenshot' => $screenshotPath,
            'notes' => $notes,
        ]);

        // Balance is NOT updated here - admin must approve deposit first

        return $transaction;
    }

    /**
     * Process withdrawal for publisher (dummy payment).
     *
     * @param  int  $publisherId
     * @param  float  $amount
     * @param  string  $paymentMethod
     * @param  array  $paymentDetails
     * @return Transaction
     */
    public function processWithdrawal(int $publisherId, float $amount, string $paymentMethod, array $paymentDetails = []): Transaction
    {
        // Generate dummy transaction ID
        $transactionId = 'TXN-' . strtoupper(Str::random(16));

        // Create transaction
        $transaction = Transaction::create([
            'transactionable_type' => \App\Models\Publisher::class,
            'transactionable_id' => $publisherId,
            'type' => 'withdrawal',
            'status' => 'completed', // Auto-complete for dummy payment
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'payment_method' => $paymentMethod,
            'payment_details' => array_merge($paymentDetails, [
                'dummy_payment' => true,
                'processed_at' => now()->toDateTimeString(),
            ]),
            'processed_at' => now(),
            'notes' => 'Dummy withdrawal processed automatically',
        ]);

        return $transaction;
    }

    /**
     * Get available payment methods.
     *
     * @return array
     */
    public function getAvailablePaymentMethods(): array
    {
        return [
            'paypal' => [
                'name' => 'PayPal (Dummy)',
                'enabled' => true,
                'auto' => true,
            ],
            'coinpayment' => [
                'name' => 'CoinPayment (Dummy)',
                'enabled' => true,
                'auto' => true,
            ],
            'faucetpay' => [
                'name' => 'FaucetPay (Dummy)',
                'enabled' => true,
                'auto' => true,
            ],
            'bank_swift' => [
                'name' => 'Bank SWIFT (Dummy)',
                'enabled' => true,
                'auto' => false,
            ],
            'manual' => [
                'name' => 'Manual Payment',
                'enabled' => true,
                'auto' => false,
            ],
        ];
    }

    /**
     * Check if payment method is enabled.
     *
     * @param  string  $method
     * @return bool
     */
    public function isPaymentMethodEnabled(string $method): bool
    {
        $methods = $this->getAvailablePaymentMethods();
        return isset($methods[$method]) && $methods[$method]['enabled'];
    }

    /**
     * Get minimum deposit amount.
     *
     * @return float
     */
    public function getMinimumDeposit(): float
    {
        return (float) Setting::get('minimum_deposit', 10.00);
    }

    /**
     * Get minimum withdrawal amount.
     *
     * @return float
     */
    public function getMinimumWithdrawal(): float
    {
        return (float) Setting::get('minimum_payout', 50.00);
    }

    /**
     * Get maximum withdrawal amount.
     *
     * @return float
     */
    public function getMaximumWithdrawal(): float
    {
        return (float) Setting::get('maximum_payout', 10000.00);
    }
}


