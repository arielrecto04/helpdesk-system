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
     * The users that belong to this helpdesk team (via employee records).
     * Note: Only returns users who have an employee record linked to this team.
     */
    public function users()
    {
        return User::whereHas('employee.helpdeskTeams', function ($query) {
            $query->where('helpdesk_teams.id', $this->id);
        });
    }

    /**
     * The employees that belong to this helpdesk team.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_helpdesk_team', 'helpdesk_team_id', 'employee_id')->withTimestamps();
    }
}