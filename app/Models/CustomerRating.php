<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class CustomerRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'customer_id',
        'assigned_to_employee_id',
        'team_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'submitted_on' => 'datetime'
    ];

    /**
     * Get the ticket that was rated.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the customer who submitted the rating.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the agent who was assigned to the ticket.
     */
    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    /**
     * Get the team that handled the ticket.
     */
    public function team()
    {
        return $this->belongsTo(HelpdeskTeam::class);
    }
}