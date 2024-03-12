<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
      return $this->hasMany('App\Models\User');
    }

    public function team_leader()
    {
        return $this->hasOne(User::class)->where('role','team_leader');
    }
    public function parent()
    {
        return $this->hasMany(DepartmentParent::class ,'department_id');
    }
    public function department()
    {
        return $this->hasMany(DepartmentParent::class ,'parent_id');

    }


    protected $hidden=['created_at','updated_at'];
}
