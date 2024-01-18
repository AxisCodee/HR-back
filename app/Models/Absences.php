<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absences extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'user_id',
        'startDate',
        'endDate',
        'duration',
        'status',
        'hours_num',
    ];
    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }


}
