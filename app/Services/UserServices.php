<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Date;
use App\Models\Late;
use App\Models\Rate;
use App\Models\User;
use App\Models\Career;
use App\Models\Absences;
use App\Models\Decision;
use App\Models\RateType;
use App\Models\Attendance;
use App\Models\UserSalary;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRequest\UpdateUserRequest;

class UserServices
{
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
        if($date){
        $rewards = Decision::where('type', 'reward')
            ->where('user_id', $user->id);
        $usertimeService = app(UserTimeService::class);
        $rewards = $usertimeService->filterDate($rewards, $date, 'dateTime');
        $totalReward = $rewards->sum('amount');
        return $totalReward;}
        return 0;
    }

    public function getAbsence($user, $date)
    {
        if($date){
        $absences = Absences::where('user_id', $user->id);
        $usertimeService = app(UserTimeService::class);
        $absences = $usertimeService->filterDate($absences, $date, 'startDate');
        $totalAbsence = $absences->count('id');
        return $totalAbsence;}
        return 0;
    }

    public function getDeduction($user, $date)
    {
        if($date){
        $deductions = Decision::where('type', 'deduction')
            ->where('user_id', $user->id);
        $usertimeService = app(UserTimeService::class);
        $deductions = $usertimeService->filterDate($deductions, $date, 'dateTime');
        $totalDeduction = $deductions->sum('amount');
        return $totalDeduction;}
        return 0;
    }

    public function getDeductions($user, $date)
    {
        $deductions = Decision::where('type', 'deduction')
            ->where('user_id', $user->id);

        $usertimeService = app(UserTimeService::class);
        $deductions = $usertimeService->filterDate($deductions, $date, 'dateTime');

        //  $totalDeduction = $deductions->sum('amount');

        return $deductions;
    }

    public function getAdvance($user, $date)
    {
        if($date){
        $advance = Decision::where('type', 'advanced')
            ->where('user_id', $user->id);
        $usertimeService = app(UserTimeService::class);
        $advance = $usertimeService->filterDate($advance, $date, 'dateTime');
        $totalAdvance = $advance->sum('amount');
        return $totalAdvance;}
        return 0;
    }

    public function getLate($user, $date)
    {
        if($date){
        $lates = Late::whereNotNull('check_in')
            ->where('type', 'Unjustified')
            ->where('user_id', $user->id);
        $usertimeService = app(UserTimeService::class);
        $lates = $usertimeService->filterDate($lates, $date, 'lateDate');
        $totalLateHours = $lates->sum('hours_num');
        return $totalLateHours;}
        return 0;
    }

    public function getOverTime($user, $date)
    {
        if($date){
        $overTimes = Late::whereNotNull('check_out')
            ->where('type', 'justified')
            ->where('user_id', $user->id);
        $usertimeService = app(UserTimeService::class);
        $overTimes = $usertimeService->filterDate($overTimes, $date, 'lateDate');
        $totalOverTimeHours = $overTimes->sum('hours_num');
        return $totalOverTimeHours;}
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
            $usertimeService = app(UserTimeService::class);
            $overTimes = $usertimeService->filterDate($overTimes, $date, 'lateDate');
            $total = $overTimes->get();
            return $total;
        }
        return [];
    }

    public function deductions($user, $date)
    {
        if ($date) {
            $deductions = Decision::where('type', 'deduction')
                ->where('user_id', $user->id);
            $usertimeService = app(UserTimeService::class);
            $deductions = $usertimeService->filterDate($deductions, $date, 'dateTime');
            $total = $deductions->get();
            return $total;
        }
        return [];
    }

    public function rewards($user, $date)
    {
        if ($date) {
            $rewards = Decision::where('type', 'reward')
                ->where('user_id', $user->id);
            $usertimeService = app(UserTimeService::class);
            $rewards = $usertimeService->filterDate($rewards, $date, 'dateTime');
            $total = $rewards->get();
            return $total;
        }
        return [];
    }

    public function advances($user, $date)
    {
        if ($date) {
            $advances = Decision::where('type', 'advance')
                ->where('user_id', $user->id);
            $usertimeService = app(UserTimeService::class);
            $advances = $usertimeService->filterDate($advances, $date, 'dateTime');
            $total = $advances->get();
            return $total;
        }
        return [];
    }

    public function warnings($user, $date)
    {
        if ($date) {
            $warning = Decision::where('type', 'warning')
                ->where('user_id', $user->id);
            $usertimeService = app(UserTimeService::class);
            $warning = $usertimeService->filterDate($warning, $date, 'dateTime');
            $total = $warning->get();
            return $total;
        }
        return [];
    }

    public function alerts($user, $date)
    {
        if ($date) {
            $alert = Decision::where('type', 'alert')
                ->where('user_id', $user->id);
            $usertimeService = app(UserTimeService::class);
            $alert = $usertimeService->filterDate($alert, $date, 'dateTime');
            $total = $alert->get();
            return $total;
        }
        return [];
    }

    public function absences($user, $date)
    {
        if ($date) {
            $absences = Absences::where('user_id', $user->id)->where('type', 'Unjustified');
            $usertimeService = app(UserTimeService::class);
            $absences = $usertimeService->filterDate($absences, $date, 'startDate');
            $total = $absences->get();
            return $total;
        }
        return [];
    }

    public function AllAbsenceTypes($request)
    {
       $userAbsence = User::query()
       ->with('justifiedAbsences','unJustifiedAbsences','sickAbsences')
       ->get()
       ->toArray();
        return $userAbsence;
    }






    public function getBaseSalary($user, $date)
    {
        if ($date) {
            $baseSalary = UserSalary::where('user_id', $user->id);
            $usertimeService = app(UserTimeService::class);
            $baseSalary = $usertimeService->filterDate($baseSalary, $date, 'date');
            $salary = $baseSalary->sum('salary');
            return $salary;
        }

        return 0;
    }
}
