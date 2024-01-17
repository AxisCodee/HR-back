<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;
    protected $fillable =
    [
        'user_id',
        'evaluator_id',
        'evaluator_role',
        'rate',
        'type'

    ];


    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function evaluators()
    {
        return $this->belongsTo(User::class,'evaluator_id');
    }


}
