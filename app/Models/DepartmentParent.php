<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentParent extends Model
{
    use HasFactory;
    protected $fillable =[
        'parent_id',
        'department_id',
        'parent_id'
    ];


public function department()
{
    return $this->belongsTo(Department::class ,'department_id');
}
public function parent()
{
    return $this->belongsTo(Department::class ,'parent_id');

}

}
