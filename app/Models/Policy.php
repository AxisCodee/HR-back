<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;
    protected $fillable = ['work_time', 'annual_salary_increase', 'warnings', 'absence_management'];

    protected $casts = [
        'work_time' => 'array',
        'annual_salary_increase' => 'array',
        'warnings' => 'array',
        'absence_management' => 'array',
    ];
}
