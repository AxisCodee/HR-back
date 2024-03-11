<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable =[
        'title',
        'name',
        'description',
        'user_id',
        'received_date',
        'path'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
