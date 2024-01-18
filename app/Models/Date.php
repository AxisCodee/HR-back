<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    protected $fillable =
    ['date'];
    use HasFactory;



    public function  pin()
    {
        return $this->belongsToMany(Attendance::class,'date_pins','date_id','pin');

    }
}
