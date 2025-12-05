<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

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
}