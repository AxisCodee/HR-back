<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Models\Absences;
use App\Models\Attendance;
use App\Models\Career;
use App\Models\Date;
use App\Models\Decision;
use App\Models\Late;
use App\Models\Rate;
use App\Models\RateType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->count('id');
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
            ->count('id');
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
        $rewards = Decision::where('type', 'reward')
            ->where('user_id', $user->id);
        $usertimeService = app(UsertimeService::class);
        $rewards = $usertimeService->checkTimeDate($rewards, $date);
        $totalReward = $rewards->sum('amount');
        return $totalReward;
    }

    public function getAbsence($user, $date)
    {
        $absences = Absences::where('user_id', $user->id);
        $usertimeService = app(UsertimeService::class);
        $absences = $usertimeService->checkAbsenceTimeDate($absences, $date);
        $totalAbsence = $absences->count('id');
        return $totalAbsence;
    }

    public function getDeduction($user, $date)
    {
        $deductions = Decision::where('type', 'deduction')
            ->where('user_id', $user->id);
        $usertimeService = app(UsertimeService::class);
        $deductions = $usertimeService->checkTimeDate($deductions, $date);
        $totalDeduction = $deductions->sum('amount');
        return $totalDeduction;
    }

    public function getDeductions($user, $date)
    {
        $deductions = Decision::where('type', 'deduction')
            ->where('user_id', $user->id);

        $usertimeService = app(UsertimeService::class);
        $deductions = $usertimeService->checkTimeDate($deductions, $date);

      //  $totalDeduction = $deductions->sum('amount');

        return $deductions;
    }
    public function getAdvance($user, $date)
    {
        $advance = Decision::where('type', 'advanced')
            ->where('user_id', $user->id);
        $usertimeService = app(UsertimeService::class);
        $advance = $usertimeService->checkTimeDate($advance, $date);
        $totalAdvance = $advance->sum('amount');
        return $totalAdvance;
    }

    public function getLate($user, $date)
    {
        $lates = Late::whereNotNull('check_in')
            ->where('type', 'Unjustified')
            ->where('user_id', $user->id);
        $usertimeService = app(UsertimeService::class);
        $lates = $usertimeService->checkOvertimeDate($lates, $date);
        $totalLateHours = $lates->sum('hours_num');
        return $totalLateHours;
    }

    public function getOverTime($user, $date)
    {
        $overTimes = Late::whereNotNull('check_out')
            ->where('type', 'justified')
            ->where('user_id', $user->id);
        $usertimeService = app(UsertimeService::class);
        $overTimes = $usertimeService->checkOvertimeDate($overTimes, $date);
        $totalOverTimeHours = $overTimes->sum('hours_num');
        return $totalOverTimeHours;
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
        return ResponseHelper::error('Error', null);
    }

    public function except_admins($branch_id)
    {
        $all_users = User::query()->where('branch_id', $branch_id)->whereNot('role', 'admin')
            ->with('department', 'userInfo:id,user_id,image')->whereNull('deleted_at')->get()->toArray();
        return $all_users;
    }

    function checkDateFormat($date)
    {
        try {
            Carbon::createFromFormat('Y', $date);
            return 'Year';
        } catch (\Exception $e) {
            try {
                Carbon::createFromFormat('Y-m', $date);
                return 'Year and Month';
            } catch (\Exception $e) {
                return 'Invalid format';
            }
        }
    }

    public function getRates($user, $date)
    {
        $user_id = $user->id;
        $branch_id = User::where('id', $user_id)->first()->branch_id;
        $rateTypes = RateType::where('branch_id', $branch_id)->get();
        $userRates = [];
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);
        if (!$month) {
            foreach ($rateTypes as $rateType) {
                $userRate = Rate::query()->where('user_id', $user_id)
                    ->whereYear('date', $date)
                    ->where('rate_type_id', $rateType->id)
                    ->get();
                $userRates[] = $userRate;
            }
        } else {
            foreach ($rateTypes as $rateType) {
                $userRate = Rate::query()->where('user_id', $user_id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('rate_type_id', $rateType->id)
                    ->get();
                $userRates[] = $userRate;
            }
        }
        return $userRates;
    }

}
