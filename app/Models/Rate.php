<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'name',
            'date'
        ];


        public function users()
        {
            return $this->belongsToMany(User::class, 'rate_users')
                ->withPivot('evaluator_id','rateType_id');
        }

        public function evaluators()
        {
            return $this->belongsToMany(User::class, 'rate_users','rate_id','evalutor_id')
                ->withPivot('user_id','rateType_id');
        }

    public function rateTypes()
    {
        return $this->belongsToMany(User::class, 'rate_users','rate_id','rateType_id')
        ->withPivot('user_id','evaluator_id');
    }
}
