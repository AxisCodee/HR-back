<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_time', 'annual_salary_increase',
<<<<<<< HEAD
        'warnings', 'absence_management', 'deduction_status', 'branch_id','demands_compensation'
=======
        'warnings', 'absence_management', 'deduction_status', 'branch_id','demands_compensation',
>>>>>>> 1b7bf896165c9b2b98cf93c007f73a7d8194c9cd
    ];

    protected $casts = [
        'work_time' => 'array',
        'annual_salary_increase' => 'array',
        'warnings' => 'array',
        'absence_management' => 'array',
    ];
}
