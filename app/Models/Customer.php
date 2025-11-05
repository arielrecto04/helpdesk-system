<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone_number',
        'department_id',
        'position_id'
    ];

    /**
     * Get all tickets created by this customer.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the customer's department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the customer's position.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}