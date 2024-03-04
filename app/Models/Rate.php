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
            'date',
            'rate',
            'rate_type_id'

        ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluators()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function rateType()
    {
        return $this->belongsTo(RateType::class, 'rate_type_id');
    }
}
