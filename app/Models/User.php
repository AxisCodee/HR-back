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
        'level'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getOverTimeAttribute()
    {
        $date = request()->query('date');
        $totalOverTimeHours = $this->userServices
            ->getOverTime($this, $date);
        return $totalOverTimeHours;
    }

    public function getOverTimesAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $overTimes = Late::whereNotNull('check_out')
                ->where('type', 'justified')
                ->where('user_id', $this->id);

            $usertimeService = app(UserTimeService::class);
            $overTimes = $usertimeService->checkOvertimeDate($overTimes, $date);

            $total = $overTimes->get();
            return $total;
        }
        return [];
    }

    public function getLateAttribute()
    {
        $date = request()->query('date');
        $totalLateHours = $this->userServices
            ->getLate($this, $date);
        return $totalLateHours;
    }

//    public function getRateAttribute()
//    {
//        $date = request()->query('date');
//        if ($date) {
//            return $this->userServices->getRates($date, $this);
//        }
//        return 0;
//    }

    public function getAdvanceAttribute()
    {
        $date = request()->query('date');
        $totalAdvance = $this->userServices
            ->getAdvance($this, $date);
        return $totalAdvance;
    }


    public function getDeductionsAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $deductions = Decision::where('type', 'deduction')
                ->where('user_id', $this->id);
            $usertimeService = app(UserTimeService::class);
            $deductions = $usertimeService->checkTimeDates($deductions, $date);
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
            $rewards = $usertimeService->checkTimeDate($rewards, $date);

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
            $advances = $usertimeService->checkTimeDate($advances, $date);
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
            $warning = $usertimeService->checkTimeDate($warning, $date);
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
            $alert = $usertimeService->checkTimeDate($alert, $date);
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
            $absences = $usertimeService->checkAbsenceTimeDate($absences, $date);

            $total = $absences->get();
            return $total;
        }
        return [];
    }


    public function getDeductionAttribute($date)
    {
        $date = request()->query('date');
        $totalDeduction = $this->userServices
            ->getDeduction($this, $date);
        return $totalDeduction;
    }

    public function getAbsenceAttribute($date)
    {
        $date = request()->query('date');
        $totalAbsence = $this->userServices
            ->getAbsence($this, $date);
        return $totalAbsence;
    }

    public function getRewardAttribute()
    {
        $date = request()->query('date');
        $totalReward = $this->userServices
            ->getReward($this, $date);
        return $totalReward;
    }

    public function getCheckInPercentageAttribute()
    {
        $date = request()->query('date');
        $percentage = $this->userServices
            ->getCheckInPercentage($this, $date);
        return $percentage;
    }

    public function getCheckOutPercentageAttribute()
    {
        $date = request()->query('date');
        $percentage = $this->userServices
            ->getCheckOutPercentage($this, $date);
        return $percentage;
    }

    public function getBaseSalaryAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            $salary = UserSalary::where('user_id', $this->id)
                ->where('date', '<=', $date)
                ->get();
            $baseSalary = $salary->isEmpty() ? 0 : $salary->last()->salary;
            return $baseSalary;
        } else {
            return 0;
        }
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
