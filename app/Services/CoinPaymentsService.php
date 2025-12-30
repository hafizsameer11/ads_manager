<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Advertiser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class CoinPaymentsService
{
    protected $merchantId;
    protected $publicKey;
    protected $privateKey;
    protected $ipnSecret;

    public function __construct()
    {
        $this->merchantId = Setting::get('coinpayments_merchant_id', '');
        $this->publicKey = Setting::get('coinpayments_public_key', '');
        $this->privateKey = Setting::get('coinpayments_private_key', '');
        $this->ipnSecret = Setting::get('coinpayments_ipn_secret', '');
    }

    /**
     * Create a CoinPayments transaction.
     *
     * @param  Advertiser  $advertiser
     * @param  float  $amount
     * @return array
     * @throws \Exception
     */
    public function createTransaction(Advertiser $advertiser, float $amount): array
    {
        // Create a pending transaction first
        $transaction = Transaction::create([
            'transactionable_type' => Advertiser::class,
            'transactionable_id' => $advertiser->id,
            'type' => 'deposit',
            'status' => 'pending',
            'amount' => $amount,
            'transaction_id' => 'TXN-' . strtoupper(Str::random(16)),
            'payment_method' => 'coinpayment',
            'payment_details' => [
                'created_at' => now()->toDateTimeString(),
            ],
            'notes' => 'CoinPayments payment pending',
        ]);

        $params = [
            'version' => 1,
            'cmd' => 'create_transaction',
            'key' => $this->publicKey,
            'format' => 'json',
            'amount' => $amount,
            'currency1' => 'USD',
            'currency2' => 'BTC', // Default to BTC, can be made configurable
            'buyer_email' => $advertiser->user->email ?? '',
            'item_name' => 'Account Deposit - $' . number_format($amount, 2),
            'item_number' => (string)$transaction->id,
            'invoice' => (string)$transaction->id,
            'custom' => (string)$transaction->id,
            'ipn_url' => route('webhooks.coinpayments'),
            'success_url' => route('dashboard.advertiser.coinpayments.success', ['transaction_id' => $transaction->id]),
            'cancel_url' => route('dashboard.advertiser.coinpayments.cancel', ['transaction_id' => $transaction->id]),
        ];

        // Generate HMAC signature
        $hmac = hash_hmac('sha512', http_build_query($params), $this->privateKey);

        try {
            $response = Http::asForm()->post('https://www.coinpayments.net/api.php', array_merge($params, [
                'hmac' => $hmac,
            ]));

            $result = $response->json();

            if ($result && isset($result['error']) && $result['error'] === 'ok') {
                // Update transaction with CoinPayments transaction ID
                $transaction->update([
                    'payment_details' => array_merge($transaction->payment_details ?? [], [
                        'coinpayments_txn_id' => $result['result']['txn_id'],
                        'coinpayments_address' => $result['result']['address'] ?? null,
                        'coinpayments_qrcode_url' => $result['result']['qrcode_url'] ?? null,
                        'coinpayments_status_url' => $result['result']['status_url'] ?? null,
                    ]),
                ]);

                return [
                    'success' => true,
                    'txn_id' => $result['result']['txn_id'],
                    'address' => $result['result']['address'] ?? null,
                    'qrcode_url' => $result['result']['qrcode_url'] ?? null,
                    'status_url' => $result['result']['status_url'] ?? null,
                    'checkout_url' => $result['result']['status_url'] ?? null,
                    'transaction_id' => $transaction->id,
                ];
            } else {
                $error = $result['error'] ?? 'Unknown error';
                $transaction->markAsFailed('CoinPayments transaction creation failed: ' . $error);
                throw new \Exception('Failed to create CoinPayments transaction: ' . $error);
            }
        } catch (\Exception $e) {
            \Log::error('CoinPayments transaction creation failed: ' . $e->getMessage());
            if (isset($transaction)) {
                $transaction->markAsFailed('CoinPayments transaction creation failed: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Verify CoinPayments IPN.
     *
     * @param  array  $payload
     * @param  string  $hmac
     * @return bool
     */
    public function verifyIPN(array $payload, string $hmac): bool
    {
        if (!$this->ipnSecret) {
            return false;
        }

        // Remove hmac from payload for verification
        $payloadForVerification = $payload;
        unset($payloadForVerification['hmac']);
        
        // Sort by key for consistent ordering
        ksort($payloadForVerification);
        
        // Reconstruct the query string
        $queryString = http_build_query($payloadForVerification);
        $calculatedHmac = hash_hmac('sha512', $queryString, $this->ipnSecret);

        return hash_equals($hmac, $calculatedHmac);
    }

    /**
     * Handle CoinPayments IPN.
     *
     * @param  array  $payload
     * @return bool
     */
    public function handleIPN(array $payload): bool
    {
        $txnId = $payload['txn_id'] ?? null;
        $status = (int)($payload['status'] ?? 0);
        $statusText = $payload['status_text'] ?? '';
        $itemNumber = $payload['item_number'] ?? $payload['invoice'] ?? $payload['custom'] ?? null;

        if (!$itemNumber) {
            \Log::error('CoinPayments IPN: Missing transaction ID');
            return false;
        }

        $transaction = Transaction::find($itemNumber);
        
        if (!$transaction) {
            \Log::error('CoinPayments IPN: Transaction not found: ' . $itemNumber);
            return false;
        }

        // Status >= 100 means payment completed
        if ($status >= 100 && $transaction->status === 'pending') {
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
                            "Your CoinPayments deposit of $" . number_format($transaction->amount, 2) . " has been completed and added to your balance.",
                            ['transaction_id' => $transaction->id, 'amount' => $transaction->amount]
                        );
                    }
                }
            });

            return true;
        } elseif ($status < 0) {
            // Negative status means payment failed
            $transaction->markAsFailed('CoinPayments payment failed: ' . $statusText);
        }

        return true;
    }
}

