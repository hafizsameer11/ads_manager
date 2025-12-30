<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Advertiser;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Str;

class StripeService
{
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = Setting::get('stripe_secret_key', '');
        
        if ($this->secretKey) {
            Stripe::setApiKey($this->secretKey);
        }
    }

    /**
     * Create a Stripe checkout session for deposit.
     *
     * @param  Advertiser  $advertiser
     * @param  float  $amount
     * @return Session
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Advertiser $advertiser, float $amount): Session
    {
        // Create a pending transaction first
        $transaction = Transaction::create([
            'transactionable_type' => Advertiser::class,
            'transactionable_id' => $advertiser->id,
            'type' => 'deposit',
            'status' => 'pending',
            'amount' => $amount,
            'transaction_id' => 'TXN-' . strtoupper(Str::random(16)),
            'payment_method' => 'stripe',
            'payment_details' => [
                'checkout_type' => 'stripe',
                'created_at' => now()->toDateTimeString(),
            ],
            'notes' => 'Stripe payment pending',
        ]);

        $successUrl = route('dashboard.advertiser.stripe.success', ['transaction_id' => $transaction->id]);
        $cancelUrl = route('dashboard.advertiser.stripe.cancel', ['transaction_id' => $transaction->id]);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Account Deposit',
                        'description' => 'Deposit funds to your advertising account',
                    ],
                    'unit_amount' => (int)($amount * 100), // Convert to cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string)$transaction->id,
            'metadata' => [
                'transaction_id' => $transaction->id,
                'advertiser_id' => $advertiser->id,
                'advertiser_email' => $advertiser->user->email ?? '',
            ],
        ]);

        // Update transaction with Stripe session ID
        $transaction->update([
            'payment_details' => array_merge($transaction->payment_details ?? [], [
                'stripe_session_id' => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
            ]),
        ]);

        return $session;
    }

    /**
     * Verify and process Stripe webhook event.
     *
     * @param  string  $payload
     * @param  string  $signature
     * @return bool
     */
    public function handleWebhook(string $payload, string $signature): bool
    {
        $webhookSecret = Setting::get('stripe_webhook_secret', '');
        
        if (!$webhookSecret) {
            \Log::error('Stripe webhook secret not configured');
            return false;
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (\Exception $e) {
            \Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return false;
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
        }

        return true;
    }

    /**
     * Handle checkout session completed event.
     */
    protected function handleCheckoutSessionCompleted($session): void
    {
        $transactionId = $session->metadata->transaction_id ?? null;
        
        if (!$transactionId) {
            \Log::error('Stripe checkout session completed but no transaction ID found');
            return;
        }

        $transaction = Transaction::find($transactionId);
        
        if (!$transaction || $transaction->status !== 'pending') {
            \Log::error('Transaction not found or already processed: ' . $transactionId);
            return;
        }

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
                        "Your Stripe deposit of $" . number_format($transaction->amount, 2) . " has been completed and added to your balance.",
                        ['transaction_id' => $transaction->id, 'amount' => $transaction->amount]
                    );
                }
            }
        });
    }

    /**
     * Handle payment intent succeeded event.
     */
    protected function handlePaymentIntentSucceeded($paymentIntent): void
    {
        // This is a backup handler in case checkout.session.completed doesn't fire
        // We'll find the transaction by payment_intent_id
        $transactions = Transaction::where('payment_method', 'stripe')
            ->where('status', 'pending')
            ->whereJsonContains('payment_details->stripe_payment_intent_id', $paymentIntent->id)
            ->get();

        foreach ($transactions as $transaction) {
            \DB::transaction(function () use ($transaction) {
                if ($transaction->status === 'pending') {
                    $transaction->markAsCompleted();
                    
                    $advertiser = $transaction->transactionable;
                    if ($advertiser instanceof Advertiser) {
                        $advertiser->increment('balance', $transaction->amount);
                    }
                }
            });
        }
    }

    /**
     * Handle payment intent failed event.
     */
    protected function handlePaymentIntentFailed($paymentIntent): void
    {
        $transactions = Transaction::where('payment_method', 'stripe')
            ->where('status', 'pending')
            ->whereJsonContains('payment_details->stripe_payment_intent_id', $paymentIntent->id)
            ->get();

        foreach ($transactions as $transaction) {
            $transaction->markAsFailed('Payment failed: ' . ($paymentIntent->last_payment_error->message ?? 'Unknown error'));
        }
    }
}

