<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone_number',
        'department_id',
        'position_id',
        'employee_code',
        'hire_date',
        'company_id',
    ];
    protected $casts = [
        'hire_date' => 'date',
    ];

    protected function firstName(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::title($value),
        );
    }

    protected function middleName(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? Str::title($value) : null,
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::title($value),
        );
    }

    /**
     * Kunin ang (opsyonal) na User account na naka-link sa employee na ito.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kunin ang department ng employee.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Kunin ang position ng employee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}