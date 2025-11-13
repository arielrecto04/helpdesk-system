<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- IDINAGDAG ITO

class Department extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'department_name',
    ];

    /**
     * Get all customers in this department.
     */
    public function customers(): HasMany // <-- Inayos ko ang return type
    {
        return $this->hasMany(Customer::class);
    }
    
    /**
     * Get all positions in this department.
     */
    // --- IDINAGDAG KO ANG BUONG FUNCTION NA ITO ---
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}