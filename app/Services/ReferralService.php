<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use App\Models\Publisher;
use App\Models\Advertiser;
use App\Models\Setting;

class ReferralService
{
    /**
     * Create referral relationship.
     *
     * @param  User  $referrer
     * @param  User  $referred
     * @param  string  $referredType
     * @return Referral
     */
    public function createReferral(User $referrer, User $referred, string $referredType = 'publisher'): Referral
    {
        // Get commission rate from settings
        $commissionRate = (float) Setting::get('referral_commission_rate', 5.00);

        return Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referred_type' => $referredType,
            'status' => 'active',
            'commission_rate' => $commissionRate,
            'total_earnings' => 0,
            'paid_earnings' => 0,
        ]);
    }

    /**
     * Process referral earnings from publisher earnings.
     *
     * @param  Publisher  $publisher
     * @param  float  $earningAmount
     * @return void
     */
    public function processPublisherReferralEarnings(Publisher $publisher, float $earningAmount): void
    {
        $user = $publisher->user;
        
        if (!$user || !$user->referred_by) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        // Get or create referral
        $referral = Referral::firstOrCreate(
            [
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'referred_type' => 'publisher',
            ],
            [
                'status' => 'active',
                'commission_rate' => (float) Setting::get('referral_commission_rate', 5.00),
                'total_earnings' => 0,
                'paid_earnings' => 0,
            ]
        );

        // Calculate and add earnings
        $referral->addEarnings($earningAmount);

        // Update referrer's balance if they are a publisher
        if ($referrer->isPublisher() && $referrer->publisher) {
            $commission = $referral->calculateEarnings($earningAmount);
            $referrer->publisher->increment('balance', $commission);
            $referrer->publisher->increment('total_earnings', $commission);
        }
    }

    /**
     * Process referral earnings from advertiser deposit.
     *
     * @param  Advertiser  $advertiser
     * @param  float  $depositAmount
     * @return void
     */
    public function processAdvertiserReferralEarnings(Advertiser $advertiser, float $depositAmount): void
    {
        $user = $advertiser->user;
        
        if (!$user || !$user->referred_by) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        // Get or create referral
        $referral = Referral::firstOrCreate(
            [
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'referred_type' => 'advertiser',
            ],
            [
                'status' => 'active',
                'commission_rate' => (float) Setting::get('referral_commission_rate', 5.00),
                'total_earnings' => 0,
                'paid_earnings' => 0,
            ]
        );

        // Calculate referral bonus (e.g., 5% of deposit)
        $bonusRate = (float) Setting::get('referral_deposit_bonus_rate', 5.00);
        $bonus = $depositAmount * ($bonusRate / 100);
        
        $referral->addEarnings($bonus);

        // Update referrer's balance if they are a publisher
        if ($referrer->isPublisher() && $referrer->publisher) {
            $referrer->publisher->increment('balance', $bonus);
            $referrer->publisher->increment('total_earnings', $bonus);
        }
    }

    /**
     * Get referral statistics for a user.
     *
     * @param  User  $user
     * @return array
     */
    public function getReferralStats(User $user): array
    {
        $referrals = Referral::where('referrer_id', $user->id)->get();

        return [
            'total_referrals' => $referrals->count(),
            'publisher_referrals' => $referrals->where('referred_type', 'publisher')->count(),
            'advertiser_referrals' => $referrals->where('referred_type', 'advertiser')->count(),
            'total_earnings' => $referrals->sum('total_earnings'),
            'paid_earnings' => $referrals->sum('paid_earnings'),
            'pending_earnings' => $referrals->sum('total_earnings') - $referrals->sum('paid_earnings'),
        ];
    }
}





