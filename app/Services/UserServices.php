<?php

namespace App\Services;

use App\Models\Absences;
use App\Models\Attendance;
use App\Models\Date;
use App\Models\Decision;
use App\Models\Late;

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
        }

        $percentage = ($checkIns / $count) * 100;

        return $percentage;
    }








    public function getCheckOutPercentage($user, $date){

    $date = request()->query('date');

    $check_outes = Attendance::where('status', '1')
        ->where('pin',  $user->pin)
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
        }

    $percentage = ($check_outes / $count) * 100;

    return $percentage;
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
            ->where('user_id', $user->id);

        $usertimeService = app(UsertimeService::class);
        $lates = $usertimeService->checkOvertimeDate($lates, $date);

        $totalLateHours = $lates->sum('hours_num');

        return $totalLateHours;
    }



    public function getOverTime($user, $date)
    {
        $overTimes = Late::whereNotNull('check_out')
            ->where('user_id', $user->id);

        $usertimeService = app(UsertimeService::class);
        $overTimes = $usertimeService->checkOvertimeDate($overTimes, $date);

        $totalOverTimeHours = $overTimes->sum('hours_num');

        return $totalOverTimeHours;
    }


}
