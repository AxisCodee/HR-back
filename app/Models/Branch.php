<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $fillable = ['name','fingerprint_scanner_ip'];


    public function  rateTypes()
    {
        return $this->hasMany(RateType::class);
    }

    public function  users()
    {
        return $this->hasMany(User::class);
    }
}
