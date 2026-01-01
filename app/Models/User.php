<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
        'referral_code',
        'referred_by',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'int', // 1 = Approved, 0 = Rejected, 2 = Pending
        'last_login_at' => 'datetime',
        'two_factor_recovery_codes' => 'array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * Get the publisher profile.
     */
    public function publisher()
    {
        return $this->hasOne(Publisher::class);
    }

    /**
     * Get the advertiser profile.
     */
    public function advertiser()
    {
        return $this->hasOne(Advertiser::class);
    }

    /**
     * Get the user who referred this user.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get users referred by this user.
     */
    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get activity logs for this user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user has a specific permission.
     * 
     * STRICT RULE: Admin (role === 'admin') always returns true.
     * Permissions are ONLY checked for Sub-Admins.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // HARD RULE: Admin bypasses ALL permission checks
        if ($this->role === 'admin' || $this->hasRole('admin')) {
            return true;
        }

        // For Sub-Admins: Check if user has permission through any of their roles
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionSlug) {
            $query->where('slug', $permissionSlug);
        })->exists();
    }

    /**
     * Check if user has any of the given permissions.
     * 
     * STRICT RULE: Admin (role === 'admin') always returns true.
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        // HARD RULE: Admin bypasses ALL permission checks
        if ($this->role === 'admin' || $this->hasRole('admin')) {
            return true;
        }

        // For Sub-Admins: Check if user has any of the permissions
        foreach ($permissionSlugs as $permissionSlug) {
            if ($this->hasPermission($permissionSlug)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Assign role to user.
     */
    public function assignRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role && !$this->hasRole($roleSlug)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove role from user.
     */
    public function removeRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Check if user is admin (by role or legacy role field).
     * DEPRECATED: Use hasAdminPermissions() instead for authorization checks.
     * This method is kept for backward compatibility only.
     */
    public function isAdmin(): bool
    {
        // Check new role system first
        if ($this->hasRole('admin')) {
            return true;
        }
        // Fallback to legacy role field for backward compatibility
        return $this->role === 'admin';
    }

    /**
     * Check if user has any admin permissions.
     * Helper method for routing and general admin checks.
     */
    public function hasAdminPermissions(): bool
    {
        return $this->hasAnyPermission([
            'manage_users',
            'approve_users',
            'manage_deposits',
            'manage_withdrawals',
            'manage_settings',
            'view_activity_logs',
            'manage_websites',
            'manage_campaigns',
            'manage_ad_units',
            'manage_roles',
        ]);
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && !empty($this->two_factor_confirmed_at);
    }

    /**
     * Check if user requires 2FA (admin or sub-admin roles).
     */
    public function requiresTwoFactor(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('sub-admin');
    }

    /**
     * Check if user is publisher.
     */
    public function isPublisher(): bool
    {
        return $this->role === 'publisher';
    }

    /**
     * Check if user is advertiser.
     */
    public function isAdvertiser(): bool
    {
        return $this->role === 'advertiser';
    }

    /**
     * Generate unique referral code.
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
