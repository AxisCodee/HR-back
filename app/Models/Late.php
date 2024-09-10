<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Late extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'status',
        'hours_num',
        'check_in',
        'check_out',
        'lateDate',
        'end',
        'moreLate',
    ];

    protected $appends = [
        'type_ar'
    ];
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function typeAr(): Attribute
    {
        return Attribute::get(function (){
            return match ($this->type){
                'justified' => 'مبرر',
                'Unjustified' => 'غير مبرر',
                'sick' => 'مريض'
            };
        });
    }
}
