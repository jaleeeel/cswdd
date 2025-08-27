<?php

// File: app/Models/Role.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Helper methods
    public function isSuperAdmin()
    {
        return $this->name === 'Super Admin';
    }

    public function isAdmin()
    {
        return $this->name === 'Admin';
    }

    public function isStaff()
    {
        return $this->name === 'Staff';
    }

    // Scopes
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    // Static methods to get roles
    public static function superAdmin()
    {
        return self::where('name', 'Super Admin')->first();
    }

    public static function admin()
    {
        return self::where('name', 'Admin')->first();
    }

    public static function staff()
    {
        return self::where('name', 'Staff')->first();
    }

    public static function getRoleOptions()
    {
        return self::pluck('name', 'id')->toArray();
    }
}
