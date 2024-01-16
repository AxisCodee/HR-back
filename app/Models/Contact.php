<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable =[
        'user_id',
        'type',
        'contact',
    ];


    public function contact()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
