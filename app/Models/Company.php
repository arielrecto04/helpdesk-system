<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
    ];

    /**
     * Kunin ang lahat ng empleyado sa ilalim ng kumpanyang ito.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Kunin ang lahat ng customers sa ilalim ng kumpanyang ito.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}