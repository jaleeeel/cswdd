<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ClientRegistration;
use Carbon\Carbon;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'email', 'phone',
        'address', 'date_of_birth', 'gender', 'client_id'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function registrations()
    {
        return $this->hasMany(ClientRegistration::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    public function getAgeAttribute()
    {
        // Fixed: Added missing 'age' property and proper null checking
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    // Alternative age calculation method that's more explicit
    public function calculateAge()
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return Carbon::parse($this->date_of_birth)->age;
    }

    // Generate unique client ID
    public static function generateClientId()
    {
        do {
            $id = 'CLT-' . strtoupper(uniqid());
        } while (self::where('client_id', $id)->exists());

        return $id;
    }

    // Additional helper methods you might find useful
    public function getInitialsAttribute()
    {
        $firstInitial = $this->first_name ? substr($this->first_name, 0, 1) : '';
        $lastInitial = $this->last_name ? substr($this->last_name, 0, 1) : '';

        return strtoupper($firstInitial . $lastInitial);
    }

    public function getGenderDisplayAttribute()
    {
        return $this->gender ? ucfirst($this->gender) : 'Not specified';
    }

    // Scope methods for filtering
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByAgeRange($query, $minAge = null, $maxAge = null)
    {
        if ($minAge) {
            $maxDate = Carbon::now()->subYears($minAge)->endOfYear();
            $query->where('date_of_birth', '<=', $maxDate);
        }

        if ($maxAge) {
            $minDate = Carbon::now()->subYears($maxAge + 1)->startOfYear();
            $query->where('date_of_birth', '>=', $minDate);
        }

        return $query;
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('client_id', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    // Boot method to auto-generate client ID if not provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->client_id)) {
                $client->client_id = self::generateClientId();
            }
        });
    }
}
