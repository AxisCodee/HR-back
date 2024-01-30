<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Date;

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
}
