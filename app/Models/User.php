<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute; 
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\HelpdeskTeam;

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

    public function assignedTickets(): HasManyThrough
    {
        // Tickets assigned to this user's employee record
        return $this->hasManyThrough(
            Ticket::class,
            Employee::class,
            'user_id', // Foreign on Employee table...
            'assigned_to_employee_id', // Foreign on Ticket table...
            'id', // Local key on User
            'id' // Local key on Employee
        );
    }

    public function createdTickets(): HasMany
    {

        return $this->hasMany(Ticket::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the employee record for this user (if exists).
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * The helpdesk teams this user belongs to (via their employee record).
     * This uses a hasManyThrough relationship via the employee's teams.
     */
    public function teams()
    {
        // If user has an employee record, get teams through employee_helpdesk_team pivot
        return $this->hasManyThrough(
            HelpdeskTeam::class,
            'App\Models\Employee',
            'user_id',      // Foreign key on employees pointing to users
            'id',           // Local key on helpdesk_teams
            'id',           // Local key on users
            'id'            // Local key on employees for joining to pivot
        )->join('employee_helpdesk_team', function($join) {
            $join->on('helpdesk_teams.id', '=', 'employee_helpdesk_team.helpdesk_team_id')
                 ->on('employees.id', '=', 'employee_helpdesk_team.employee_id');
        });
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermissionTo($permissionName)
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->flatMap(function ($role) {
                return $role->permissions;
            })->contains('name', $permissionName);
        }
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }

    public function customer()
    {
        return $this->hasOne(\App\Models\Customer::class);
    }
}