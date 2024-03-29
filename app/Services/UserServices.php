<?php

namespace App\Services;


use App\Models\Date;
use App\Models\Late;
use App\Models\Policy;
use App\Models\User;
use App\Models\Career;
use App\Models\Absences;
use App\Models\Decision;
use App\Models\Attendance;
use App\Models\UserSalary;
use App\Helper\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserRequest\UpdateUserRequest;

class UserServices
{


    protected $userTimeService;

    public function __construct(UserTimeService $userTimeService)
    {
        $this->userTimeService = $userTimeService;
    }


    public function getCheckInPercentage($user, $date)
    {
        $checkIns = Attendance::where('status', '0')
            ->where('pin', $user->pin)
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
            ->selectRaw('COUNT(DISTINCT CONCAT(pin, DATE(datetime))) as check_ins')
            ->value('check_ins');
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);
            $dates = Date::query();
            if ($day) {
                $dates->whereDate('date', $date);
            } elseif ($month) {
                $dates->whereYear('date', $year)
                    ->whereMonth('date', $month);
            } else {
                $dates->whereYear('date', $year);
            }
            $count = $dates->count('id');
            if ($count == 0) {
                $percentage = 0;
            } else {
                $percentage = ($checkIns / $count) * 100;
            }
            return $percentage;
        } else
            return 0;
    }

    public function getCheckOutPercentage($user, $date)
    {

        $date = request()->query('date');
        $checkOut = Attendance::where('status', '1')
            ->where('pin', $user->pin)
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
            ->selectRaw('COUNT(DISTINCT CONCAT(pin, DATE(datetime))) as check_outs')
            ->value('check_outs');
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);
            $dates = Date::query();
            if ($day) {
                $dates->whereDate('date', $date);
            } elseif ($month) {
                $dates->whereYear('date', $year)
                    ->whereMonth('date', $month);
            } else {
                $dates->whereYear('date', $year);
            }
            $count = $dates->count('id');
            if ($count == 0) {
                $percentage = 0;
            } else {
                $percentage = ($checkOut / $count) * 100;
            }
            return $percentage;
        } else
            return 0;
    }

    public function getReward($user, $date)
    {
        if ($date) {
            $rewards = Decision::where('type', 'reward')
                ->where('user_id', $user->id);
            $rewards = $this->userTimeService->filterDate($rewards, $date, 'dateTime');
            $totalReward = $rewards->sum('amount');
            return $totalReward;
        }
        return 0;
    }

    public function getAbsence($user, $date)
    {
        if ($date) {
            $absences = Absences::where('user_id', $user->id);
            $absences = $this->userTimeService->filterDate($absences, $date, 'startDate');
            $totalAbsence = $absences->count('id');
            return $totalAbsence;
        }
        return 0;
    }

    public function getDeduction($user, $date)
    {
        if ($date) {
            $deductions = Decision::where('type', 'deduction')
                ->where('user_id', $user->id);
            $deductions = $this->userTimeService->filterDate($deductions, $date, 'dateTime');
            $totalDeduction = $deductions->sum('amount');
            return $totalDeduction;
        }
        return 0;
    }

    public function getDeductions($user, $date)
    {
        $deductions = Decision::where('type', 'deduction')
            ->where('user_id', $user->id);
        $deductions = $this->userTimeService->filterDate($deductions, $date, 'dateTime');

        //  $totalDeduction = $deductions->sum('amount');

        return $deductions;
    }

    public function getAdvance($user, $date)
    {
        if ($date) {
            $advance = Decision::where('type', 'advanced')
                ->where('user_id', $user->id);
            $advance = $this->userTimeService->filterDate($advance, $date, 'dateTime');
            $totalAdvance = $advance->sum('amount');
            return $totalAdvance;
        }
        return 0;
    }

    public function getLate($user, $date)
    {
        if ($date) {
            $lates = Late::whereNotNull('check_in')
                ->where('type', 'Unjustified')
                ->where('user_id', $user->id);
            $lates = $this->userTimeService->filterDate($lates, $date, 'lateDate');
            $totalLateHours = $lates->sum('hours_num');
            return $totalLateHours;
        }
        return 0;
    }

    public function getOverTime($user, $date)
    {
        if ($date) {
            $overTimes = Late::whereNotNull('check_out')
                ->where('type', 'justified')
                ->where('user_id', $user->id);
            $usertimeService = app(UserTimeService::class);
            $overTimes = $usertimeService->filterDate($overTimes, $date, 'lateDate');
            $totalOverTimeHours = $overTimes->sum('hours_num');
            return $totalOverTimeHours;
        }
        return 0;
    }

    public function editUser(UpdateUserRequest $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $specUser = User::findOrFail($id);
            if ($specUser->role != $request->role) {
                $addExp = Career::create([
                    'user_id' => $id,
                    'content' => 'worked as a ' . $specUser->role,
                ]);
            }
            $specUser->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => $request->role,
                'department_id' => $request->department_id,
            ]);
            return ResponseHelper::success($specUser, null, 'user info updated successfully', 200);

        });
    }

    public function except_admins($branch_id)
    {
        $all_users = User::query()->where('branch_id', $branch_id)->whereNot('role', 'admin')
            ->with('department', 'userInfo:id,user_id,image')->whereNull('deleted_at')->get()->toArray();
        return $all_users;
    }

    public function UpdateSalary($request, $user)
    {
        $user->update(['salary' => $request->salary]);
        $newsalary = UserSalary::create([
            'date' => now()->format('Y-m'),
            'salary' => $request->salary,
            'user_id' => $user->id,
        ]);
    }

    public function branchWorkHours($branch_id)
    {
        $policy = Policy::query()->where('branch_id', $branch_id)->first();
        $startTime = Carbon::createFromFormat('h:i A', $policy->work_time['start_time']);
        $endTime = Carbon::createFromFormat('h:i A', $policy->work_time['end_time']);
        return $endTime->diffInHours($startTime);
    }

    public function compensationHours($user)
    {
        $branch_id = $user->branch_id;
        $branchWorkHours = $this->branchWorkHours($branch_id);
        $userDelays = Late::query()->where('user_id', $user->id)->sum('hours_num');
        dd($userDelays);
        $userAbsence = Absences::query()->where('user_id', $user->id)->count();
        return intval($userDelays + ($userAbsence * $branchWorkHours));
    }

    public function employeeHourPrice($user)
    {
        $policy = Policy::query()->where('branch_id', $user->branch_id)->first();
        if ($policy != null) {
            $userSalary = $user->userInfo()->value('salary');
            $branchWorkHours = $policy->monthlyhours;
            return ($userSalary / $branchWorkHours);
        }
        return false;
    }

    /***
     *
     *        ^^^^^^^^^^^^^^^^^^^^^^^^^^^
     **********USER Arrays **********
     */
    public function overTimes($user, $date)
    {
        if ($date) {
            $overTimes = Late::whereNotNull('check_out')
                ->where('type', 'justified')
                ->where('user_id', $user->id);
            $overTimes = $this->userTimeService->filterDate($overTimes, $date, 'lateDate');
            return $overTimes->get();
        }
        return [];
    }

    public function deductions($user, $date)
    {
        if ($date) {
            $deductions = Decision::where('type', 'deduction')
                ->where('user_id', $user->id);
            $deductions = $this->userTimeService->filterDate($deductions, $date, 'dateTime');
            return $deductions->get();
        }
        return [];
    }

    public function rewards($user, $date)
    {
        if ($date) {
            $rewards = Decision::where('type', 'reward')
                ->where('user_id', $user->id);
            $rewards = $this->userTimeService->filterDate($rewards, $date, 'dateTime');
            return $rewards->get();
        }
        return [];
    }

    public function advances($user, $date)
    {
        if ($date) {
            $advances = Decision::where('type', 'advance')
                ->where('user_id', $user->id);
            $advances = $this->userTimeService->filterDate($advances, $date, 'dateTime');
            return $advances->get();
        }
        return [];
    }

    public function warnings($user, $date)
    {
        if ($date) {
            $warning = Decision::where('type', 'warning')
                ->where('user_id', $user->id);
            $warning = $this->userTimeService->filterDate($warning, $date, 'dateTime');
            return $warning->get();
        }
        return [];
    }

    public function alerts($user, $date)
    {
        if ($date) {
            $alert = Decision::where('type', 'alert')
                ->where('user_id', $user->id);
            $alert = $this->userTimeService->filterDate($alert, $date, 'dateTime');
            return $alert->get();
        }
        return [];
    }

    public function absences($user, $date)
    {
        if ($date) {
            $absences = Absences::where('user_id', $user->id);
            $absences = $this->userTimeService->filterDate($absences, $date, 'startDate');
            return $absences->get();
        }
        return [];
    }

    public function AllAbsenceTypes($request)
    {
        return User::query()
            ->with('justifiedAbsences', 'unJustifiedAbsences', 'sickAbsences')
            ->get()
            ->toArray();
    }

    public function getBaseSalary($user, $date)
    {
        if ($date) {
            $baseSalary = UserSalary::where('user_id', $user->id);
            $baseSalary = $this->userTimeService
                ->filterDate($baseSalary, $date, 'date')->sum('salary');;
            return $baseSalary;
        }
        return 0;
    }
}
