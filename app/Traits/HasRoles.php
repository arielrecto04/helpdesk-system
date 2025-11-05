<?php

namespace App\Traits;

trait HasRoles
{
    /**
     * Get the roles assigned to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'user_role');
    }

    /**
     * Check if the user has a specific role
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }
}