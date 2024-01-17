<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable =[
        'title',
        'user_id',
        'received_date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
