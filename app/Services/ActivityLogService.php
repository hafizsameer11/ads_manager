<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log an activity.
     *
     * @param  string  $action
     * @param  string  $description
     * @param  mixed  $subject
     * @param  array  $properties
     * @param  Request|null  $request
     * @return ActivityLog
     */
    public static function log(
        string $action,
        string $description,
        $subject = null,
        array $properties = [],
        ?Request $request = null
    ): ActivityLog {
        $user = Auth::user();
        $request = $request ?? request();

        return ActivityLog::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'properties' => $properties,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
        ]);
    }

    /**
     * Log user creation.
     */
    public static function logUserCreated(User $user, ?User $createdBy = null): ActivityLog
    {
        $description = "User '{$user->name}' ({$user->email}) was created";
        if ($createdBy) {
            $description .= " by {$createdBy->name}";
        }

        return self::log('user.created', $description, $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'created_by' => $createdBy ? $createdBy->id : null,
        ]);
    }

    /**
     * Log user update.
     */
    public static function logUserUpdated(User $user, array $changes = []): ActivityLog
    {
        return self::log('user.updated', "User '{$user->name}' was updated", $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'changes' => $changes,
        ]);
    }

    /**
     * Log user approval.
     */
    public static function logUserApproved(User $user, ?User $approvedBy = null): ActivityLog
    {
        $description = "User '{$user->name}' was approved";
        if ($approvedBy) {
            $description .= " by {$approvedBy->name}";
        }

        return self::log('user.approved', $description, $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'approved_by' => $approvedBy ? $approvedBy->id : null,
        ]);
    }

    /**
     * Log user rejection.
     */
    public static function logUserRejected(User $user, ?User $rejectedBy = null, ?string $reason = null): ActivityLog
    {
        $description = "User '{$user->name}' was rejected";
        if ($rejectedBy) {
            $description .= " by {$rejectedBy->name}";
        }

        return self::log('user.rejected', $description, $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'rejected_by' => $rejectedBy ? $rejectedBy->id : null,
            'reason' => $reason,
        ]);
    }

    /**
     * Log user suspension.
     */
    public static function logUserSuspended(User $user, ?User $suspendedBy = null, ?string $reason = null): ActivityLog
    {
        $description = "User '{$user->name}' was suspended";
        if ($suspendedBy) {
            $description .= " by {$suspendedBy->name}";
        }

        return self::log('user.suspended', $description, $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'suspended_by' => $suspendedBy ? $suspendedBy->id : null,
            'reason' => $reason,
        ]);
    }

    /**
     * Log deposit approval.
     */
    public static function logDepositApproved($transaction, ?User $approvedBy = null): ActivityLog
    {
        $description = "Deposit #{$transaction->id} of $" . number_format($transaction->amount, 2) . " was approved";
        if ($approvedBy) {
            $description .= " by {$approvedBy->name}";
        }

        return self::log('deposit.approved', $description, $transaction, [
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'approved_by' => $approvedBy ? $approvedBy->id : null,
        ]);
    }

    /**
     * Log deposit rejection.
     */
    public static function logDepositRejected($transaction, ?User $rejectedBy = null, ?string $reason = null): ActivityLog
    {
        $description = "Deposit #{$transaction->id} of $" . number_format($transaction->amount, 2) . " was rejected";
        if ($rejectedBy) {
            $description .= " by {$rejectedBy->name}";
        }

        return self::log('deposit.rejected', $description, $transaction, [
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'rejected_by' => $rejectedBy ? $rejectedBy->id : null,
            'reason' => $reason,
        ]);
    }

    /**
     * Log withdrawal approval.
     */
    public static function logWithdrawalApproved($withdrawal, ?User $approvedBy = null): ActivityLog
    {
        $description = "Withdrawal #{$withdrawal->id} of $" . number_format($withdrawal->amount, 2) . " was approved";
        if ($approvedBy) {
            $description .= " by {$approvedBy->name}";
        }

        return self::log('withdrawal.approved', $description, $withdrawal, [
            'withdrawal_id' => $withdrawal->id,
            'amount' => $withdrawal->amount,
            'approved_by' => $approvedBy ? $approvedBy->id : null,
        ]);
    }

    /**
     * Log website approval.
     */
    public static function logWebsiteApproved($website, ?User $approvedBy = null): ActivityLog
    {
        $description = "Website '{$website->domain}' was approved";
        if ($approvedBy) {
            $description .= " by {$approvedBy->name}";
        }

        return self::log('website.approved', $description, $website, [
            'website_id' => $website->id,
            'domain' => $website->domain,
            'approved_by' => $approvedBy ? $approvedBy->id : null,
        ]);
    }

    /**
     * Log campaign approval.
     */
    public static function logCampaignApproved($campaign, ?User $approvedBy = null): ActivityLog
    {
        $description = "Campaign '{$campaign->name}' was approved";
        if ($approvedBy) {
            $description .= " by {$approvedBy->name}";
        }

        return self::log('campaign.approved', $description, $campaign, [
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'approved_by' => $approvedBy ? $approvedBy->id : null,
        ]);
    }

    /**
     * Log login attempt.
     */
    public static function logLoginAttempt(string $email, bool $success, ?string $reason = null): ActivityLog
    {
        $description = $success 
            ? "Successful login attempt for {$email}"
            : "Failed login attempt for {$email}" . ($reason ? ": {$reason}" : '');

        return self::log('auth.login', $description, null, [
            'email' => $email,
            'success' => $success,
            'reason' => $reason,
        ]);
    }

    /**
     * Log settings update.
     */
    public static function logSettingsUpdate(string $section, array $changes, ?User $updatedBy = null): ActivityLog
    {
        $description = "Settings section '{$section}' was updated";
        if ($updatedBy) {
            $description .= " by {$updatedBy->name}";
        }

        return self::log('settings.updated', $description, null, [
            'section' => $section,
            'changes' => $changes,
            'updated_by' => $updatedBy ? $updatedBy->id : null,
        ]);
    }

    /**
     * Log 2FA enabled.
     */
    public static function logTwoFactorEnabled(User $user, ?User $enabledBy = null): ActivityLog
    {
        $description = "Two-factor authentication enabled for user '{$user->name}'";
        if ($enabledBy && $enabledBy->id !== $user->id) {
            $description .= " by {$enabledBy->name}";
        }

        return self::log('two_factor.enabled', $description, $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'enabled_by' => $enabledBy ? $enabledBy->id : null,
        ]);
    }

    /**
     * Log 2FA disabled.
     */
    public static function logTwoFactorDisabled(User $user, ?User $disabledBy = null): ActivityLog
    {
        $description = "Two-factor authentication disabled for user '{$user->name}'";
        if ($disabledBy && $disabledBy->id !== $user->id) {
            $description .= " by {$disabledBy->name}";
        }

        return self::log('two_factor.disabled', $description, $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'disabled_by' => $disabledBy ? $disabledBy->id : null,
        ]);
    }

    /**
     * Log failed 2FA verification attempt.
     */
    public static function logTwoFactorFailed(User $user, ?string $reason = null): ActivityLog
    {
        $description = "Failed 2FA verification attempt for user '{$user->name}'";
        if ($reason) {
            $description .= ": {$reason}";
        }

        return self::log('two_factor.verification_failed', $description, $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'reason' => $reason,
        ]);
    }

    /**
     * Log successful 2FA verification.
     */
    public static function logTwoFactorVerified(User $user): ActivityLog
    {
        return self::log('two_factor.verification_success', "Successful 2FA verification for user '{$user->name}'", $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);
    }
}

