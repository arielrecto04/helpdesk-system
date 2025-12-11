<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\User;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'customer_id',
        'team_id',
        'assigned_to_employee_id',
        'priority',
        'stage',
        'deadline',
        'closed_at'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($ticket) {
            // If ticket is being set to a closed/resolved stage, record closed_at
            if (in_array($ticket->stage, ['Resolved', 'Closed'])) {
                if (empty($ticket->closed_at)) {
                    $ticket->closed_at = now();
                }
            } else {
                // If stage moved away from closed/resolved, clear closed_at
                if ($ticket->isDirty('stage') && !empty($ticket->closed_at)) {
                    $ticket->closed_at = null;
                }
            }
        });
    }

    /**
     * Get the customer that created the ticket.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the team assigned to the ticket.
     */
    public function team()
    {
        return $this->belongsTo(HelpdeskTeam::class, 'team_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_ticket');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    /**
     * Apply visibility filters based on the authenticated user's permissions.
     *
     * Rules when user lacks permissions:
     * - can_view_other_locations_tickets: Only tickets whose assigned employee's `company_id`
     *   matches the current employee's `company_id` will be included.
     * - can_view_other_teams_tickets: Only tickets that belong to the user's helpdesk teams will be included.
     * - can_view_other_users_tickets: Only tickets assigned to the current employee will be included.
     *
     * Note: Unassigned tickets are excluded when any of the above restrictions apply,
     * as they are not assigned to a specific employee/location.
     */
    public function scopeVisibleTo($query, User $user)
    {
        // If user has all three permissions enabled, show everything
        $hasAllViewPerms = $user->hasPermissionTo('can_view_other_locations_tickets')
            && $user->hasPermissionTo('can_view_other_teams_tickets')
            && $user->hasPermissionTo('can_view_other_users_tickets');

        if ($hasAllViewPerms) {
            return $query;
        }

        // Prefer linked employee by user_id, fall back to email
        $employee = Employee::where('user_id', $user->id)->with(['company', 'helpdeskTeams'])->first();
        if (! $employee && $user->email) {
            $employee = Employee::where('email', $user->email)->with(['company', 'helpdeskTeams'])->first();
        }

        if (! $employee) {
            // If user has no employee record, show nothing by default
            return $query->whereNull('id');
        }

        $restrictByLocation = ! $user->hasPermissionTo('can_view_other_locations_tickets');
        $restrictByTeams    = ! $user->hasPermissionTo('can_view_other_teams_tickets');
        $restrictByUsers    = ! $user->hasPermissionTo('can_view_other_users_tickets');

        // When restricting by users, only show tickets assigned to current employee
        if ($restrictByUsers) {
            $query->where('assigned_to_employee_id', $employee->id);
        }

        // When restricting by teams, only include tickets from user's helpdesk teams
        if ($restrictByTeams) {
            $teamIds = $employee->helpdeskTeams ? $employee->helpdeskTeams->pluck('id')->all() : [];
            if (! empty($teamIds)) {
                $query->whereIn('team_id', $teamIds);
            } else {
                // No team memberships means no visible tickets
                $query->whereNull('id');
            }
        }

        // When restricting by location, match assigned employee's company_id to current employee's company_id
        if ($restrictByLocation && $employee->company_id) {
            $companyId = $employee->company_id;
            $query->whereHas('assignedTo', function ($empQuery) use ($companyId) {
                $empQuery->where('company_id', $companyId);
            });
        }

        return $query;
    }
}