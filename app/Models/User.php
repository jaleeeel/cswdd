<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;
use App\Models\Department;
use App\Models\ClientRegistration;
use App\Models\Report;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
        'is_active',
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
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
    ];
    //Relationships
    public function role()
{
    return $this->belongsTo(Role::class);
}

public function isSuperAdmin()
{
    return $this->role && strtolower($this->role->name) === 'super_admin';
}

public function isAdmin()
{
    return $this->role && strtolower($this->role->name) === 'admin';
}

public function isStaff()
{
    return $this->role && strtolower($this->role->name) === 'staff';
}

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function clientRegistrations()
    {
        return $this->hasMany(ClientRegistration::class, 'registered_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
    // Helper methods

    public function canManageDepartment($departmentId = null)
    {
        if ($this->isSuperAdmin()) return true;
        if ($this->isAdmin()) {
            return $departmentId ? $this->department_id == $departmentId : true;
        }
        return false;
    }
}

