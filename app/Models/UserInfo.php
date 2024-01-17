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
        'certificates',
        'salary',
    ];

    protected $casts = [
        'certificates' => 'array',
        'study_situation' => 'array'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function address()
    // {
    //     return $this->hasOne(Address::class);
    // }

    // public function careers()
    // {
    //     return $this->hasMany(Career::class);
    // }
    // public function deposits()
    // {
    //     return $this->hasMany(Deposit::class);
    // }
    // public function contacts()
    // {
    //     return $this->hasMany(Contact::class);
    // }


}
