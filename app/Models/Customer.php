<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone_number',
        'company_id',
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

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}