<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalFile extends Model
{
    use HasFactory;
    protected $fillable =
    [
        'user_id',
        'description',
        'path',
    ];

    public function user_files()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
