<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles, SoftDeletes;

    //protected $with = ['department'];
    protected $fillable =
    [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'role',
        'department_id',
        'password',
        'address',
        'specialization',
        'pin',
        'provider_id',
        'provider_name',
        'google_access_token_json',
    ];


    // protected $appends = ['overTime', 'rate', 'late', 'deduction'];
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // public function getOverTimeAttribute($value)
    // {
    //     $date = request()->query('date');
    //     if ($date) {
    //         $lates = Late::whereNotNull('check_out')
    //             ->whereMonth('lateDate', date('m', strtotime($date)))
    //             ->whereYear('lateDate', date('Y', strtotime($date)))
    //             ->where('user_id', $this->id)
    //             ->sum('hours_num');
    //         return $lates;
    //     }
    //     return 0; // إرجاع القيمة صفر في حالة عدم إرسال التاريخ
    // }


    // public function getRateAttribute($value)
    // {
    //     $date = request()->query('date');
    //     if ($date) {
    //         // $lates = Late::whereNotNull('check_out')
    //         // ->whereMonth('lateDate', date('m', strtotime($date)))
    //         //     ->whereYear('lateDate', date('Y', strtotime($date)))
    //         //     ->where('user_id', $this->id)
    //         //     ->sum('hours_num');
    //         // return $lates;
    //     }
    //     return 0; // إرجاع القيمة صفر في حالة عدم إرسال التاريخ
    // }

    // public function getAdvancesAttribute($value)
    // {
    //     $date = request()->query('date');
    //     if ($date) {
    //         $advances = Decision::where('type', 'deduction')
    //             ->where('user_id', $this->id);
    //         if (strpos($date, 'day') !== false) {
    //             $advances->whereDay('dateTime', date('d', strtotime($date)));
    //         } elseif (strpos($date, 'month') !== false) {
    //             $advances->whereMonth('dateTime', date('m', strtotime($date)));
    //         } elseif (strpos($date, 'year') !== false) {
    //             $advances->whereYear('dateTime', date('Y', strtotime($date)));
    //         }
    //         $totalAdvances = $advances->sum('amount');
    //         return $totalAdvances;
    //     }
    //     return 0;
    // }
    // public function getDeductionAttribute($value)
    // {
    //     $date = request()->query('date');
    //     if ($date) {
    //         $deductions = Decision::where('type', 'deduction')
    //             ->where('user_id', $this->id);
    //         if (strpos($date, 'day') !== false) {
    //             $deductions->whereDay('dateTime', date('d', strtotime($date)));
    //         } elseif (strpos($date, 'month') !== false) {
    //             $deductions->whereMonth('dateTime', date('m', strtotime($date)));
    //         } elseif (strpos($date, 'year') !== false) {
    //             $deductions->whereYear('dateTime', date('Y', strtotime($date)));
    //         }
    //         $totalDeductions = $deductions->sum('amount');
    //         return $totalDeductions;
    //     }
    //     return 0;
    // }
    // public function getLatesAttribute($value)
    // {
    //     $date = request()->query('date');
    //     if ($date) {
    //         $deductions = Decision::where('type', 'deduction')
    //             ->where('user_id', $this->id);
    //         if (strpos($date, 'day') !== false) {
    //             $deductions->whereDay('dateTime', date('d', strtotime($date)));
    //         } elseif (strpos($date, 'month') !== false) {
    //             $deductions->whereMonth('dateTime', date('m', strtotime($date)));
    //         } elseif (strpos($date, 'year') !== false) {
    //             $deductions->whereYear('dateTime', date('Y', strtotime($date)));
    //         }
    //         $totalDeductions = $deductions->sum('amount');
    //         return $totalDeductions;
    //     }
    //     return 0;
    // }
    // public function getUserAbsence($date)
    // {
    //     // $today = Carbon::now();
    //     // if ($today->eq($date)) {
    //     //     $this->cuurentAbsence();
    //     // } else {
    //     $date = request()->query('date');
    //         $day = substr($date, 8, 2);
    //         //$user = User::query()->get();
    //         $result = $this->absences()
    //             ->whereDay('startDate', $day)->get();
    //         return $result;
    //     //}
    // }
    // public function getRateAttribute()
    // {
    //     $coachId = $this->id;

    //     $totalRating = Rate::query()
    //         ->where('coachId', $coachId)
    //         ->sum('rate');

    //     $userCount = Rate::query()
    //         ->where('coachId', $coachId)
    //         ->count('playerId');

    //     if ($userCount === 0) {
    //         return 0;
    //     }

    //     $averageRating = $totalRating / $userCount;

    //     return intval($averageRating);
    // }



    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function attendance()
    {
        return $this->hasMany('App\Models\Attendance', 'pin', 'pin');
    }

    public function my_files()
    {
        return $this->hasMany(AdditionalFile::class, 'user_id', 'id');
    }

    public function contract()
    {
        return $this->hasMany(Contract::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department');
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function my_decisions()
    {
        return $this->hasMany(Decision::class, 'user_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function my_team()
    {
        return $this->hasMany(User::class, 'department_id', 'department_id')->where('role', 'employee')->with('userInfo');
    }

    public function my_contacts()
    {
        return $this->hasMany(Contact::class, 'user_id', 'id');
    }

    public function Requests()
    {
        return $this->hasMany(Request::class, 'user_id');
    }
    // public function getRoleAttribute()
    // {
    //     return $this->getRoleNames()->first();
    // }

    public function userRates()
    {
        return $this->hasMany(Rate::class, 'user_id');
    }

    public function evaluatorRates()
    {
        return $this->hasMany(Rate::class, 'evaluator_id');
    }

    public function  absences()
    {
        return $this->hasMany(Absences::class, 'user_id');
    }

    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function careers()
    {
        return $this->hasMany(Career::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function  notes()
    {
        return $this->hasMany(Note::class);
    }

    public function  languages()
    {
        return $this->hasMany(Language::class);
    }

    public function  certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function  study_situations()
    {
        return $this->hasMany(StudySituation::class);
    }

    public function  empOfMonths()
    {
        return $this->hasMany(EmpOfMonth::class);
    }
}
