<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = ['path','startTime','endTime','user_id'];
    use HasFactory;



    public function user()
    {
      return $this->belongsTo(User::class, 'user_id');
    }

    public function userInBranch()
{
    return $this->belongsTo(User::class)->where('branch_id', $this->branch_id);
}
}
