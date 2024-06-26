<?php

namespace App\Models;


use App\Services\AbsenceService;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    protected $absenceService;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $userTimeService = new UserTimeService();
        $fileService = new FileService();
        $this->userServices = new UserServices($userTimeService, $fileService);
        $this->usertimeService = new UserTimeService();
        $this->absenceService = new AbsenceService($userTimeService);
    }


    protected $fillable = [
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
        // 'deductions',
        //'rewards',
        //'advances',
        'warnings',
        'OverTimes',
        //'alerts',
        'status',
        'level',
        'isTrash',
        'dismissed',
        'TotalAbsenceHours',
        'totalCompensationHours',
        'isabsent',
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
        $overTime =    $this->userServices->getOverTime($this, $date);
        $hourPrice = $this->userServices->calculateEmpHour($this, $date);
        return $overTime * $hourPrice;
    }


    public function getLateAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->getLate($this, $date);
    }


    public function getAdvanceAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->getAdvance($this, $date);
    }

    public function getDeductionAttribute($date)
    {
        $date = request()->query('date');
        return $this->userServices->getDeduction($this, $date);
    }

    public function getAbsenceAttribute($date)
    {
        $date = request()->query('date');
        return $this->userServices->getAbsence($this, $date);
    }

    public function getRewardAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->getReward($this, $date);
    }

    public function getCheckInPercentageAttribute()
    {
        $date = request()->query('date');
        if (strlen($date) == 10) {
            return null;
        }
        return $this->userServices->getCheckInPercentage($this, $date);
    }

    public function getCheckOutPercentageAttribute()
    {
        $date = request()->query('date');
        if (strlen($date) == 10) {
            return null;
        }
        return $this->userServices->getCheckOutPercentage($this, $date);
    }

    public function getIsAbsentAttribute()
    {
        $date = Carbon::now()->format('Y-m-d');
        return $this->absenceService->absenceStatus($this->id, $date);
    }
    /***
     *
     *   ^^^^^^^^^^^^^^^^^^^^^^^^^^
     **********USER SERVICE **********
     */


    /***
     *
     *
     *
     *
     *
     **********USER ABSENCE RELATIONSHIP **********
     */

    public function UnPaidAbsences() //Un paid
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id')
            ->where('isPaid', 0);
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }

    public function PaidAbsences() //Paid
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id')
            ->where('isPaid', 1);
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }

    public function sickAbsences() //Sick
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id')
            ->where('type', 'sick');
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }


    public function allAbsences() //for All user absence
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id');
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }


    /*
   ----------------ABSENCES COUNT------------------
   */
    public function justifiedPaidAbsencesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id')
            ->where('isPaid', 1)->where('type', 'justified');
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }

    public function justifiedUnPaidAbsencesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id')
            ->where('type', 'justified')->where('isPaid', 0);
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }

    public function UnjustifiedPaidAbsencesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id')
            ->where('type', 'Unjustified')->where('isPaid', 1);
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }

    public function UnjustifiedUnPaidAbsencesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Absences::class, 'user_id')
            ->where('type', 'Unjustified')->where('isPaid', 0);
        return $this->usertimeService->filterDate($result, $date, 'startDate');
    }

    /***
     *
     *        ^^^^^^^^^^^^^^^^^^^^^^^^^^^
     **********USER ABSENCE RELATIONSHIP **********
     */


    /***
     *
     *
     *
     *
     *
     **********USER LATE RELATIONSHIP **********
     */

    public function UnPaidLates() //Un paid
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')->where('isPaid', 0);
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }

    public function PaidLates() //Paid
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')->where('isPaid', 1);
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }


    public function sickLates() //Sick
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')->where('type', 'sick');
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }

    public function totalLates() //for All lates
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')->whereNotNull('check_in');
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }

    public function allLates() //for All User Lates
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id');
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }


    /*
   ----------------LATES COUNT------------------
   */
    public function justifiedPaidLatesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')
            ->where('isPaid', 1)
            ->where('type', 'justified');
        $this->usertimeService->filterDate($result, $date, 'lateDate');

        return $result;
    }

    public function justifiedUnPaidLatesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')
            ->where('type', 'justified')->where('isPaid', 0);
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }

    public function UnjustifiedPaidLatesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')
            ->where('type', 'Unjustified')->where('isPaid', 1);
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }

    public function UnjustifiedUnPaidLatesCount()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Late::class, 'user_id')
            ->where('type', 'Unjustified')->where('isPaid', 0);
        return $this->usertimeService->filterDate($result, $date, 'lateDate');
    }


    public function deductions()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Decision::class, 'user_id')
            ->where('type', 'deduction');
        return $this->usertimeService->filterDate($result, $date, 'dateTime');
    }

    public function rewards()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Decision::class, 'user_id')
            ->where('type', 'reward');
        return $this->usertimeService->filterDate($result, $date, 'dateTime');
    }

    public function advances()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Decision::class, 'user_id')
            ->where('type', 'advance');
        return $this->usertimeService->filterDate($result, $date, 'dateTime');
    }

    public function warnings()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Decision::class, 'user_id')
            ->where('type', 'warning');
        return $this->usertimeService->filterDate($result, $date, 'dateTime');
    }
    //   public function overTimes()
    // {
    //     $date = request()->query('date');
    //     $result = $this->hasMany(Decision::class, 'user_id')
    //         ->where('type', 'overTime');
    //     return $this->usertimeService->filterDate($result, $date, 'lateDate');
    // }

    public function alerts()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Decision::class, 'user_id')
            ->where('type', 'alert');
        return $this->usertimeService->filterDate($result, $date, 'dateTime');
    }

    public function absences()
    {
        $date = request()->query('date');
        $result = $this->hasMany(Decision::class, 'user_id')
            ->where('type', 'deduction');
        return $this->usertimeService->filterDate($result, $date, 'dateTime');
    }


    /***
     *
     *        ^^^^^^^^^^^^^^^^^^^^^^^^^^^
     **********USER LATE RELATIONSHIP **********
     */


    public function getOverTimesAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->overTimes($this, $date);
    }


    public function getDeductionsAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->deductions($this, $date);
    }


    public function getRewardsAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->rewards($this, $date);
    }

    public function getAdvancesAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->advances($this, $date);
    }

    public function getWarningsAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->warnings($this, $date);
    }

    public function getAlertsAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->alerts($this, $date);
    }

    // public function getAbsencesAttribute()
    // {
    //     $date = request()->query('date');
    //     return $this->userServices->absences($this, $date);
    // }


    public function getBaseSalaryAttribute()
    {
        $date = request()->query('date');
        return $this->userServices->getBaseSalary($this, $date);
    }

    public function getTotalAbsenceHoursAttribute()
    {
        $date = request()->query('date');
        if ($date) {
            return $this->absenceService->totalAbsenceHours($this->id, $date);
        }
    }

    public function getStatusAttribute()
    {
        $datetime = Carbon::now()->format('Y-m-d');
        $status = Attendance::query()
            ->where('pin', $this->pin)
            ->where('branch_id', $this->branch_id)
            ->whereRaw('DATE(datetime) = ? ', [$datetime])
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

    public function getIsTrashAttribute()
    {
        return $this->deleted_at === null ? false : true;
    }


    public function getLevelAttribute()
    {
        return $this->userInfo()->value('level');
    }

    //*******************
    public function getTotalCompensationHoursAttribute()
    {
        $demandCompensationHours = $this->userInfo()->value('compensation_hours');
        $totalCompensationHours = $this->userServices->compensationHours($this);
        if ($demandCompensationHours && $totalCompensationHours) {
            return $totalCompensationHours - $demandCompensationHours;
        }
        return 0;
    }

    //*******************
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function my_decisions()
    {
        return $this->hasMany(Decision::class, 'user_id', 'id');
    }

    public function penalties()
    {
        return $this->hasMany(Decision::class, 'user_id', 'id')
            ->where('type', 'penalty');
    }

    public function salary()
    {
        return $this->hasMany(UserSalary::class, 'user_id');
    }

    public function permissions(): BelongsToMany
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


    // public function userRates()
    // {
    //     return $this->hasMany(Rate::class, 'user_id');
    // }


    // public function evaluatorRates()
    // {
    //     return $this->hasMany(Rate::class, 'evaluator_id');
    // }

    public function absence()
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

    public function evaluatorRates()
    {
        return $this->belongsToMany(Rate::class, 'rate_users', 'evalutor_id')
            ->withPivot('evaluator_id', 'rateType_id');
    }

    public function userRateTypes()
    {
        return $this->belongsToMany(RateType::class, 'rate_users', 'user_id', 'rateType_id')
            ->withPivot('rate_id', 'evalutor_id', 'rate');
    }

    public function evaluatorRateTypes()
    {
        return $this->belongsToMany(RateType::class, 'rate_users', 'evalutor_id', 'rateType_id')
            ->withPivot('rate_id', 'user_id', 'rate');
    }
}
