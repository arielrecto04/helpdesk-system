<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpdeskTeam extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'team_name'
    ];

    /**
     * Get the users in this team.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_team');
    }

    /**
     * Get the tickets assigned to this team.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'team_id');
    }
}