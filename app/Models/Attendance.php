<?php

namespace App\Models;

use App\Services\AbsenceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'pin', 'pin');
    }

    public function dates()
    {
        return $this->belongsToMany(Date::class, 'date_pins', 'pin', 'pin');
    }


}
