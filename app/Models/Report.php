<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Department;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'data', 'type', 'generated_by',
        'department_id', 'report_period_start', 'report_period_end'
    ];

    protected $casts = [
        'data' => 'array',
        'report_period_start' => 'date',
        'report_period_end' => 'date',
    ];

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
