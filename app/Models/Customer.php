<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Idinagdag ko 'to para malinis
use Illuminate\Database\Eloquent\Relations\HasMany; // Idinagdag ko 'to para malinis
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

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

    // --- ⬇️ IDINAGDAG KO ANG MGA FUNCTIONS DITO ⬇️ ---

    /**
     * Awtomatikong i-capitalize ang first name bago i-save.
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            // Ang 'set' ay ginagamit kapag nagse-save ng data
            set: fn ($value) => Str::title($value),
        );
    }

    /**
     * Awtomatikong i-capitalize ang middle name bago i-save.
     */
    protected function middleName(): Attribute
    {
        return Attribute::make(
            // Che-check muna natin kung may laman; kung wala, 'null' lang
            set: fn ($value) => $value ? Str::title($value) : null,
        );
    }

    /**
     * Awtomatikong i-capitalize ang last name bago i-save.
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::title($value),
        );
    }

    // --- ⬆️ HANGGANG DITO ⬆️ ---


    /**
     * Get all tickets created by this customer.
     */
    public function tickets(): HasMany // Inayos ko ang return type
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the customer's department.
     */
    public function department(): BelongsTo // Inayos ko ang return type
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the customer's position.
     */
    public function position(): BelongsTo // Inayos ko ang return type
    {
        return $this->belongsTo(Position::class);
    }
}