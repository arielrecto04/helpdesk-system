<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Models\Employee;

class HelpdeskTeam extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'team_name'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'team_id');
    }

    /**
     * The users that belong to this helpdesk team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_team', 'helpdesk_team_id', 'user_id');
    }

    /**
     * The employees that belong to this helpdesk team.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_helpdesk_team', 'helpdesk_team_id', 'employee_id')->withTimestamps();
    }
}