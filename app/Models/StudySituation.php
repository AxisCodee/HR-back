<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudySituation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'degree',
        'study',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
