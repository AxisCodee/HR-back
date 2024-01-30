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
use App\Services\UsertimeService;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles, SoftDeletes;


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


    protected $appends = ['deduction','reward','advance','overtime','absence','late','check_in'];
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getOverTimeAttribute()
    {
        $overTimes = Late::whereNotNull('check_out')
            ->where('user_id', $this->id);

        $date = request()->query('date');

        $overtimeService = app(UsertimeService::class);
        $overTimes = $overtimeService->checkOvertimeDate($overTimes, $date);

        $totalLateHours = $overTimes->sum('hours_num');
        return $totalLateHours;
    }




    public function getLateAttribute()
    {
        $lates = Late::whereNotNull('check_in')
            ->where('user_id', $this->id);

        $date = request()->query('date');

        $overtimeService = app(UsertimeService::class);
        $lates = $overtimeService->checkOvertimeDate($lates, $date);

        $totalLateHours = $lates->sum('hours_num');
        return $totalLateHours;
    }



    public function getRateAttribute($value)//not ready
    {
        $date = request()->query('date');
        if ($date) {
            // $lates = Late::whereNotNull('check_out')
            // ->whereMonth('lateDate', date('m', strtotime($date)))
            //     ->whereYear('lateDate', date('Y', strtotime($date)))
            //     ->where('user_id', $this->id)
            //     ->sum('hours_num');
            // return $lates;
        }
        return 0; // إرجاع القيمة صفر في حالة عدم إرسال التاريخ
    }

    public function getAdvanceAttribute()
    {
        $date = request()->query('date');

            $advance = Decision::where('type', 'advanced')
                ->where('user_id', $this->id);
        $advanced = app(UsertimeService::class);
        $advance = $advanced->checkTimeDate($advance, $date);
      $totalAdvance =$advance->sum('amount');

            return $totalAdvance;


    }
    public function getDeductionAttribute($date)
{
    $date = request()->query('date');

        $deductions = Decision::where('type', 'deduction')
            ->where('user_id', $this->id);
            $deduction = app(UsertimeService::class);
            $deductions = $deduction->checkTimeDate($deductions, $date);
          $totalDeduction =$deductions->sum('amount');

                return $totalDeduction;

}


public function getAbsenceAttribute($date)
{
    $date = request()->query('date');

        $abcences = Absences::where('user_id', $this->id);
            $abcence = app(UsertimeService::class);
            $abcence = $abcence->checkAbsenceTimeDate($abcences, $date);
          $totalAbsence=$abcences->count('id');

                return $totalAbsence;

}


    public function getRewardAttribute()
    {
        $date = request()->query('date');
            $rewards = Decision::where('type', 'reward')
                ->where('user_id', $this->id);
                $reward = app(UsertimeService::class);
                $rewards = $reward->checkTimeDate($rewards, $date);
              $totalReward =$rewards->sum('amount');

                    return $totalReward;

    }





    public function getCheckInPercentageAttribute()
    {
        $date = request()->query('date');

        $check_outes = Attendance::where('status', '0')
            ->where('pin', $this->pin)
            ->when($date, function ($query, $date) {
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);

                if ($month) {
                    return $query->whereYear('datetime', $year)
                        ->whereMonth('datetime', $month);
                } else {
                    return $query->whereYear('datetime', $year);
                }
            })
            ->count('id');

        $dates = Attendance::where('status', '0')
            ->when($date, function ($query, $date) {
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);

                if ($month) {
                    return $query->whereYear('datetime', $year)
                        ->whereMonth('datetime', $month);
                } else {
                    return $query->whereYear('datetime', $year);
                }
            })
            ->distinct('datetime')
            ->count('id');

        $percentage = ($check_outes / $dates) * 100;

        return $percentage;
    }
    public function getCheckOutPercentageAttribute()
    {
        $overTimes = Attendance::whereNotNull('check_out')
            ->where('user_id', $this->id);

        $date = request()->query('date');

        $overtimeService = app(UsertimeService::class);
        $overTimes = $overtimeService->checkOvertimeDate($overTimes, $date);

        $totalLateHours = $overTimes->sum('hours_num');
        return $totalLateHours;
    }



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
