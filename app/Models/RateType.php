<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateType extends Model
{
    use HasFactory;
    protected $fillable =
    [
        'branch_id',
        'rate_type',
    ];
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function rate()
    {
        return $this->hasMany(Rate::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'rate_users','rateType_id','user_id')
        ->withPivot('rate_id','evalutor_id');
    }
}
