<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Check if role has a permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Give permission to role.
     */
    public function givePermission(string $permissionSlug): void
    {
        $permission = Permission::where('slug', $permissionSlug)->first();
        if ($permission && !$this->hasPermission($permissionSlug)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Revoke permission from role.
     */
    public function revokePermission(string $permissionSlug): void
    {
        $permission = Permission::where('slug', $permissionSlug)->first();
        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }
}
