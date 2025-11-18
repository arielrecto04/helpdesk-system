<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'customer_id',
        'team_id',
        'assigned_to_user_id',
        'priority',
        'stage',
        'deadline'
    ];

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
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_ticket');
    }
}