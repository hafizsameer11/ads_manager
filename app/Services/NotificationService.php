<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for admin users.
     *
     * @param  string  $type
     * @param  string  $category
     * @param  string  $title
     * @param  string  $message
     * @param  array  $data
     * @return void
     */
    public static function notifyAdmins(string $type, string $category, string $title, string $message, array $data = []): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'notifiable_type' => User::class,
                'notifiable_id' => $admin->id,
                'type' => $type,
                'category' => $category,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => false,
            ]);
        }
    }

    /**
     * Create a notification for a specific user.
     *
     * @param  User  $user
     * @param  string  $type
     * @param  string  $category
     * @param  string  $title
     * @param  string  $message
     * @param  array  $data
     * @return Notification
     */
    public static function notifyUser(User $user, string $type, string $category, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $type,
            'category' => $category,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify publisher about withdrawal processing status.
     *
     * @param  User  $user
     * @param  int  $withdrawalId
     * @param  float  $amount
     * @param  string  $status (approved, rejected, processed)
     * @param  string|null  $rejectionReason
     * @return Notification
     */
    public static function notifyWithdrawalProcessing(User $user, int $withdrawalId, float $amount, string $status, ?string $rejectionReason = null): Notification
    {
        $statusMessages = [
            'approved' => [
                'title' => 'Withdrawal Approved',
                'message' => "Your withdrawal request of $" . number_format($amount, 2) . " has been approved and is being processed.",
            ],
            'rejected' => [
                'title' => 'Withdrawal Rejected',
                'message' => "Your withdrawal request of $" . number_format($amount, 2) . " has been rejected. " . ($rejectionReason ? "Reason: {$rejectionReason}. " : "") . "The amount has been refunded to your account.",
            ],
            'processed' => [
                'title' => 'Withdrawal Processed',
                'message' => "Your withdrawal of $" . number_format($amount, 2) . " has been processed and payment has been sent.",
            ],
        ];

        $messageData = $statusMessages[$status] ?? [
            'title' => 'Withdrawal Update',
            'message' => "Your withdrawal request of $" . number_format($amount, 2) . " status has been updated.",
        ];

        return self::notifyUser(
            $user,
            'withdrawal_' . $status,
            'withdrawal',
            $messageData['title'],
            $messageData['message'],
            [
                'withdrawal_id' => $withdrawalId,
                'amount' => $amount,
                'status' => $status,
                'rejection_reason' => $rejectionReason,
            ]
        );
    }

    /**
     * Notify advertiser about campaign approval/rejection.
     *
     * @param  User  $user
     * @param  int  $campaignId
     * @param  string  $status (approved, rejected)
     * @param  string|null  $rejectionReason
     * @return Notification
     */
    public static function notifyCampaignApproval(User $user, int $campaignId, string $status, ?string $rejectionReason = null): Notification
    {
        $campaign = \App\Models\Campaign::find($campaignId);
        $campaignName = $campaign ? $campaign->name : "Campaign #{$campaignId}";
        
        $statusMessages = [
            'approved' => [
                'title' => 'Campaign Approved',
                'message' => "Your campaign '{$campaignName}' has been approved and is now active.",
            ],
            'rejected' => [
                'title' => 'Campaign Rejected',
                'message' => "Your campaign '{$campaignName}' has been rejected." . ($rejectionReason ? " Reason: {$rejectionReason}." : ""),
            ],
        ];

        $messageData = $statusMessages[$status] ?? [
            'title' => 'Campaign Update',
            'message' => "Your campaign '{$campaignName}' status has been updated.",
        ];

        return self::notifyUser(
            $user,
            'campaign_' . $status,
            'campaign',
            $messageData['title'],
            $messageData['message'],
            [
                'campaign_id' => $campaignId,
                'status' => $status,
                'rejection_reason' => $rejectionReason,
            ]
        );
    }

    /**
     * Notify advertiser about account approval/rejection.
     *
     * @param  User  $user
     * @param  string  $status (approved, rejected)
     * @return Notification
     */
    public static function notifyAdvertiserApproval(User $user, string $status): Notification
    {
        $statusMessages = [
            'approved' => [
                'title' => 'Account Approved',
                'message' => "Your advertiser account has been approved. You can now create campaigns and deposit funds.",
            ],
            'rejected' => [
                'title' => 'Account Rejected',
                'message' => "Your advertiser account has been rejected. Please contact support for more information.",
            ],
        ];

        $messageData = $statusMessages[$status] ?? [
            'title' => 'Account Update',
            'message' => "Your advertiser account status has been updated.",
        ];

        return self::notifyUser(
            $user,
            'advertiser_' . $status,
            'user',
            $messageData['title'],
            $messageData['message'],
            [
                'status' => $status,
            ]
        );
    }
}
