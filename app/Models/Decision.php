<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'user_id',
        'type',
        'content',
        'amount',
        'salary',
        'fromSystem',
        'dateTime',
        'branch_id'
    ];


    public function user_decision()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
