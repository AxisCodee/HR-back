<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public function user()
    {
      return $this->hasMany('App\Models\User');
    }

    protected $hidden=['created_at','updated_at','id'];
}
