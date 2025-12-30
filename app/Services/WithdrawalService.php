<?php

namespace App\Services;

use App\Models\Withdrawal;
use App\Models\Publisher;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create withdrawal request.
     *
     * @param  Publisher  $publisher
     * @param  float  $amount
     * @param  string  $paymentMethod
     * @param  array  $paymentDetails
     * @return Withdrawal
     */
    public function createWithdrawal(Publisher $publisher, float $amount, string $paymentMethod, array $paymentDetails = []): Withdrawal
    {
        // Validate minimum and maximum withdrawal
        $minimumWithdrawal = $this->paymentService->getMinimumWithdrawal();
        $maximumWithdrawal = $this->paymentService->getMaximumWithdrawal();
        
        if ($amount < $minimumWithdrawal) {
            throw new \Exception("Minimum withdrawal amount is {$minimumWithdrawal}");
        }
        
        if ($amount > $maximumWithdrawal) {
            throw new \Exception("Maximum withdrawal amount is {$maximumWithdrawal}");
        }

        // Check if publisher has sufficient balance
        if ($publisher->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        // Check if there's a pending withdrawal
        $pendingWithdrawal = Withdrawal::where('publisher_id', $publisher->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingWithdrawal) {
            throw new \Exception('You already have a pending withdrawal request');
        }

        // Prepare withdrawal data
        $withdrawalData = [
            'publisher_id' => $publisher->id,
            'amount' => $amount,
            'payment_method' => 'manual', // All withdrawals are now manual
            'payment_details' => $paymentDetails,
            'status' => 'pending',
        ];

        // Add account type details
        if (isset($paymentDetails['account_type_id'])) {
            $withdrawalData['account_type'] = $paymentDetails['account_type'] ?? null;
            $withdrawalData['account_name'] = $paymentDetails['account_name'] ?? null;
            $withdrawalData['account_number'] = $paymentDetails['account_number'] ?? null;
        }

        // Create withdrawal
        $withdrawal = Withdrawal::create($withdrawalData);

        // Deduct from publisher balance
        $publisher->decrement('balance', $amount);

        return $withdrawal;
    }

    /**
     * Approve and process withdrawal.
     *
     * @param  Withdrawal  $withdrawal
     * @return bool
     */
    public function approveWithdrawal(Withdrawal $withdrawal): bool
    {
        return DB::transaction(function () use ($withdrawal) {
            // Mark withdrawal as approved
            $withdrawal->approve();

            // Process payment (dummy)
            $transaction = $this->paymentService->processWithdrawal(
                $withdrawal->publisher_id,
                $withdrawal->amount,
                $withdrawal->payment_method,
                $withdrawal->payment_details
            );

            // Update withdrawal with transaction ID
            $withdrawal->update([
                'notes' => "Processed via transaction: {$transaction->transaction_id}",
            ]);

            return true;
        });
    }

    /**
     * Reject withdrawal and refund balance.
     *
     * @param  Withdrawal  $withdrawal
     * @param  string  $reason
     * @return bool
     */
    public function rejectWithdrawal(Withdrawal $withdrawal, string $reason): bool
    {
        return DB::transaction(function () use ($withdrawal, $reason) {
            // Reject withdrawal
            $withdrawal->reject($reason);

            // Refund balance to publisher
            $publisher = $withdrawal->publisher;
            $publisher->increment('balance', $withdrawal->amount);

            return true;
        });
    }

    /**
     * Get withdrawal statistics for publisher.
     *
     * @param  Publisher  $publisher
     * @return array
     */
    public function getWithdrawalStats(Publisher $publisher): array
    {
        $totalWithdrawn = Withdrawal::where('publisher_id', $publisher->id)
            ->where('status', 'approved')
            ->sum('amount');

        $pendingWithdrawals = Withdrawal::where('publisher_id', $publisher->id)
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'total_withdrawn' => $totalWithdrawn,
            'pending_withdrawals' => $pendingWithdrawals,
            'available_balance' => $publisher->balance,
            'minimum_withdrawal' => $this->paymentService->getMinimumWithdrawal(),
        ];
    }
}










