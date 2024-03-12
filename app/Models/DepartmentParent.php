<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentParent extends Model
{
    use HasFactory;
    protected $fillable =[
        'parent_id',
        'department_id'
    ];


public function parents()
{
    return $this->belongsToMany(Department::class ,'department_id');
}
public function departments()
{
    return $this->belongsToMany(Department::class ,'parent_id');

}

}
