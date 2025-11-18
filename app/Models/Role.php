<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the users with this role.
     */
    

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}