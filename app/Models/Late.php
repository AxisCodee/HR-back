<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Late extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'status',
        'hours_num',
        'check_in',
        'check_out',
        'lateDate',
        'moreLate',



    ];
    use HasFactory;
}
