<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image',
        'birth_date',
        'gender',
        'nationalID',
        'social_situation',
        'study_situation',
        'military_situation',
        'level',
        'salary',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
