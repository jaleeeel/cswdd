<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;

class ClientRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'service_id', 'registered_by', 'status',
        'registration_date', 'service_date', 'notes'
    ];

    protected $casts = [
        'registration_date' => 'date',
        'service_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->whereHas('service.program', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }
}
