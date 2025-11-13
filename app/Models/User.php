<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute; 
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Awtomatikong i-capitalize ang first name bago i-save.
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::title($value),
        );
    }

    /**
     * Awtomatikong i-capitalize ang middle name bago i-save.
     */
    protected function middleName(): Attribute
    {
        return Attribute::make(
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

    /**
     * Get the roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }


    /**
     * Get the teams this user is a member of.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(HelpdeskTeam::class, 'user_team', 'user_id', 'team_id');
    }

    /**
     * Get the tickets assigned to this user.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to_user_id');
    }

    public function createdTickets(): HasMany
    {

        return $this->hasMany(Ticket::class);
    }
}