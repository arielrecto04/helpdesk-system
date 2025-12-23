<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Customer;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'customer_id',
        'team_id',
        'employee_id',
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
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Backwards-compatible alias for the assigned employee relation.
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_ticket');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }


    public function scopeVisibleTo($query, $user)
    {
        // Prefer linked employee by user_id, fall back to email
        $employee = Employee::where('user_id', $user->id)->with(['company', 'helpdeskTeams'])->first();

        // If no employee record exists
        if (! $employee) {
            // Special permission: can view tickets even without Employee record
            if ($user->hasPermissionTo('can_view_tickets_even_not_employee')) {
                return $query;
            }
            // Otherwise, show nothing
            return $query->whereNull('id');
        }

        $restrictByLocation = ! $user->hasPermissionTo('can_view_other_locations_tickets');

        // When restricting by location, match assigned employee's company's address
        if ($restrictByLocation) {
            $companyAddress = optional($employee->company)->address;
            if (empty($companyAddress)) {
                return $query->whereNull('id');
            }
            $query->whereHas('assignedTo', function ($empQuery) use ($companyAddress) {
                $empQuery->whereHas('company', function ($q) use ($companyAddress) {
                    $q->where('address', $companyAddress);
                });
            });
        }

        return $query;
    }
}