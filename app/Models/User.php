<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use App\Services\UserTimeService;
use App\Services\UserServices;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles, SoftDeletes;

    protected $userServices;
    protected $usertimeService;


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->userServices = new UserServices();
        $this->usertimeService = new UserTimeService();
    }

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
            'branch_id'
        ];


    protected $appends = [
        'deduction',
        'reward',
        'advance',
        'overtime',
        'absence',
        'late',
        'CheckInPercentage',
        'CheckOutPercentage',
        'BaseSalary',
        'deductions',
        'rewards',
        'advances',
        'absences',
        'warnings',
        'overTimes',
        'alerts',
        'status',
        'level',
        'isTrash',
        'dismissed',
        'TotalAbsenceHours'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



     /* **********GO TO USER SERVICE**********
     *
        [userServises :
         late,
        overtime,
        absence,
        advance,
        reward,
        deduction,
        warning,
        checkinpercentage,
        checkoutpercentage,

        ]
     *
     */
    public function getOverTimeAttribute()
    {
        $date = request()->query('date');
        if ($date) {
        $totalOverTimeHours = $this->userServices
            ->getOverTime($this, $date);
        return $totalOverTimeHours;}
        return 0;
    }


    public function getLateAttribute()
    {
        $date = request()->query('date');
        if($date){
        $totalLateHours = $this->userServices
            ->getLate($this, $date);
        return $totalLateHours;}
        return 0;
    }


    public function getAdvanceAttribute()
    {
        $date = request()->query('date');
        if ($date) {
        $totalAdvance = $this->userServices
            ->getAdvance($this, $date);
        return $totalAdvance;}
        return 0;
    }

    public function getDeductionAttribute($date)
    {
        $date = request()->query('date');
        if ($date) {
        $totalDeduction = $this->userServices
            ->getDeduction($this, $date);
        return $totalDeduction;}
        return 0;
    }


    public function getAbsenceAttribute($date)
    {
        if ($date) {
        $date = request()->query('date');
        $totalAbsence = $this->userServices
            ->getAbsence($this, $date);
        return $totalAbsence;}
        return 0;
    }

    public function getRewardAttribute()
    {
        $date = request()->query('date');
        if ($date) {
        $totalReward = $this->userServices
            ->getReward($this, $date);
        return $totalReward;}
        return 0;
    }

    public function getCheckInPercentageAttribute()
    {
        $date = request()->query('date');
        if ($date) {
        $percentage = $this->userServices
            ->getCheckInPercentage($this, $date);
        return $percentage;}
        return 0;
    }

    public function getCheckOutPercentageAttribute()
    {
        $date = request()->query('date');
        if ($date) {
        $percentage = $this->userServices
            ->getCheckOutPercentage($this, $date);
        return $percentage;}
        return 0;
    }

    /***
     *
     *   ^^^^^^^^^^^^^^^^^^^^^^^^^^
     **********USER SERVICE **********
     */




  /***
     *
     *
     **********USER ABSENCE RELATIONSHIP **********
     */

     public function justifiedAbsences()
     {
       $date = request()->query('date');
       $result = $this->hasMany(Absences::class, 'user_id')
       ->where('type','justified');
       return      $this->usertimeService->filterDate($result,$date,'startDate');
         }


     public function unJustifiedAbsences()
     {
         $date = request()->query('date');
         $result = $this->hasMany(Absences::class, 'user_id')
         ->where('type','UnJustified');
          return      $this->usertimeService->filterDate($result,$date,'startDate');
            }

     public function sickAbsences()
     {
         $date = request()->query('date');
         $result = $this->hasMany(Absences::class, 'user_id')
         ->where('type','sick');
         return      $this->usertimeService->filterDate($result,$date,'startDate');
        }


  /***
     *
     *        ^^^^^^^^^^^^^^^^^^^^^^^^^^^
     **********USER ABSENCE RELATIONSHIP **********
     */













    public function getOverTimesAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $overTimes = Late::whereNotNull('check_out')
                ->where('type', 'justified')
                ->where('user_id', $this->id);

            $usertimeService = app(UserTimeService::class);
            $overTimes = $usertimeService->filterDate($overTimes, $date,'lateDate');

            $total = $overTimes->get();
            return $total;
        }
        return [];
    }










    public function getDeductionsAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $deductions = Decision::where('type', 'deduction')
                ->where('user_id', $this->id);
            $usertimeService = app(UserTimeService::class);
            $deductions = $usertimeService->filterDate($deductions, $date,'dateTime');
            $total = $deductions->get();
            return $total;
        }
        return [];
    }


    public function getRewardsAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $rewards = Decision::where('type', 'reward')
                ->where('user_id', $this->id);

            $usertimeService = app(UserTimeService::class);
            $rewards = $usertimeService->filterDate($rewards, $date,'dateTime');

            $total = $rewards->get();
            return $total;
        }
        return [];
    }

    public function getAdvancesAttribute()

    {
        $date = request()->query('date');
        if ($date) {
            $advances = Decision::where('type', 'advance')
                ->where('user_id', $this->id);
            $usertimeService = app(UserTimeService::class);
            $advances = $usertimeService->filterDate($advances, $date,'dateTime');
            $total = $advances->get();
            return $total;
        }
        return [];
    }

    public function getWarningsAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $warning = Decision::where('type', 'warning')
                ->where('user_id', $this->id);

            $usertimeService = app(UserTimeService::class);
            $warning = $usertimeService->filterDate($warning, $date, 'dateTime');
            $total = $warning->get();
            return $total;
        }
        return [];
    }
    public function getAlertsAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $alert = Decision::where('type', 'alert')
                ->where('user_id', $this->id);

            $usertimeService = app(UserTimeService::class);
            $alert = $usertimeService->filterDate($alert, $date,'dateTime');
            $total = $alert->get();
            return $total;
        }
        return [];
    }

    public function getAbsencesAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $absences = Absences::where('user_id', $this->id)->where('type', 'Unjustified');

            $usertimeService = app(UserTimeService::class);
            $absences = $usertimeService->filterDate($absences, $date,'startDate');

            $total = $absences->get();
            return $total;
        }
        return [];
    }






    public function getBaseSalaryAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $incomingDate = Carbon::parse($date);
            $today = Carbon::today();
            if ($incomingDate->lte($today)) {
                $salary = UserSalary::where('user_id', $this->id)
                    ->where('date', '<=', $date)
                    ->sum('salary');
                //$baseSalary = $salary->isEmpty() ? 0 : $salary->last()->salary;
                return $salary;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getTotalAbsenceHoursAttribute()
    {
        $latehours = Late::where('user_id',$this->id)->count('hours_num');

        $branchpolicy = Policy::where('branch_id',$this->branch_id)->first();

        $startTime = Carbon::parse($branchpolicy->work_time['start_time']);
        $endTime = Carbon::parse($branchpolicy->work_time['end_time']);
        $worktime = $startTime->diffInMinutes($endTime, false);

       //  $worktime = $worktime%60;

        $absence = Absences::where('user_id',$this->id)
                            ->whereNot('isPaid',1)
                            ->whereNot('type','justified')
                            ->count();

        $absencehours = $absence * $worktime;
        $totalhours = $absencehours + $latehours;
        return $worktime;
        //$absencehours = Absences::where('user_id',$this->id);
    }

    public function getStatusAttribute()
    {
        $datetime = Carbon::now();
        $status = Attendance::query()
            ->where('pin', $this->pin)
            ->whereDate('datetime', '=', $datetime)
            ->whereTime('datetime', '<=', Carbon::parse($datetime)->format('H:i:s'))
            ->latest()
            ->value('status');
        return $status;
    }

    public function getDismissedAttribute()
    {
        $userPolicy = Policy::query()->where('branch_id', $this->branch_id)->first();
        $userAlerts = UserAlert::query()->where('user_id', $this->id)->sum('alert');

        if ($userPolicy && $userPolicy->warnings['warnings_to_dismissal'] - 1 <= $userAlerts) {
            return true;
        }
        return false;
    }


    public function getCompensationAttribute()
    {
        $date = Carbon::now();
        $lates = $this->userServices
            ->getLate($this, $date);



        $totalLateHours = $this->userServices
            ->getLate($this, $date);
        return $totalLateHours;
    }

    public function getIsTrashAttribute()
    {
        return $this->deleted_at === null ? false : true;
    }


    public function getLevelAttribute()
    {
        return $this->userInfo()->value('level');
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

    public function skills()
    {
        return $this->hasMany(Skills::class, 'user_id');
    }

    public function contract()
    {
        return $this->hasMany(Contract::class, 'user_id');
    }

    public function alert()
    {
        return $this->hasMany(UserAlert::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department');
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function my_decisions()
    {
        return $this->hasMany(Decision::class, 'user_id', 'id');
    }


    // public function Warnings()
    // {
    //     return $this->hasMany(Decision::class, 'user_id', 'id')->where('type','warning');
    // }

    // public function  Deductions()
    // {
    //     return $this->hasMany(Decision::class, 'user_id', 'id')->where('type','deduction');
    // }

    // public function Rewards()
    // {
    //     return $this->hasMany(Decision::class, 'user_id', 'id')->where('type','reward');
    // }

    public function penalties()
    {
        return $this->hasMany(Decision::class, 'user_id', 'id')
            ->where('type', 'penalty');
    }

    public function salary()
    {
        return $this->hasMany(UserSalary::class, 'user_id');
    }

    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function my_team()
    {
        return $this->hasMany(User::class, 'department_id', 'department_id')
            ->where('role', 'employee')
            ->with('userInfo');
    }


    public function my_contacts()
    {
        return $this->hasMany(Contact::class, 'user_id', 'id');
    }

    public function phoneNumber()
    {
        return $this->hasMany(Contact::class, 'user_id', 'id')
            ->whereNotNull('phone_num');
    }

    public function emails()
    {
        return $this->hasMany(Contact::class, 'user_id', 'id')
            ->whereNotNull('email');
    }

    public function emergency()
    {
        return $this->hasMany(Contact::class, 'user_id', 'id')
            ->where('type', 'emergency');
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

    public function absences()
    {
        return $this->hasMany(Absences::class, 'user_id');
    }










    public function userInfo()
    {
        return $this->hasOne(UserInfo::class, 'user_id');
    }

    public function isAdmin()
    {
        if (Auth()->user()->role == 'admin')
            return true;
        else  return false;
    }

    public function careers()
    {
        return $this->hasMany(Career::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'user_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function study_situations()
    {
        return $this->hasMany(StudySituation::class);
    }

    public function empOfMonths()
    {
        return $this->hasMany(EmpOfMonth::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
