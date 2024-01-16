<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Report extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'user_id',
        'content'
    ];


    public function user_reports()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
