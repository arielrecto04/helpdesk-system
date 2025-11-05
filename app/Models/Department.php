<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'department_name'
    ];

    /**
     * Get all customers in this department.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}