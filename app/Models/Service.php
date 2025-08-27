<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Program;
use App\Models\ClientRegistration;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'program_id', 'is_active', 'fee'];

    protected $casts = [
        'is_active' => 'boolean',
        'fee' => 'decimal:2',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function clientRegistrations()
    {
        return $this->hasMany(ClientRegistration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDepartment()
    {
        return $this->program->department;
    }
}
