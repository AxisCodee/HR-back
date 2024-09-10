<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absences extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'isPaid',
        'user_id',
        'startDate',
        'endDate',
        'duration',
        'status',
        'hours_num',
        'dayNumber',
        'demands_compensation'
    ];

    protected $appends = [
        'type_ar'
    ];

    public function users(): BelongsTo
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
