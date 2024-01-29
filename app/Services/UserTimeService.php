<?php

namespace App\Services;

use App\Models\Late;

class UsertimeService
{
    public function checkOvertimeDate($lates, $date)
    {
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);

            if ($day) {
                $lates->whereDate('lateDate', $date);
            } elseif ($month) {
                $lates->whereYear('lateDate', $year)
                    ->whereMonth('lateDate', $month);
            } else {
                $lates->whereYear('lateDate', $year);
            }
        }

        return $lates;
    }

    public function checkTimeDate($lates, $date)
    {
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);

            if ($day) {
                $lates->whereDate('dateTime', $date);
            } elseif ($month) {
                $lates->whereYear('dateTime', $year)
                    ->whereMonth('dateTime', $month);
            } else {
                $lates->whereYear('dateTime', $year);
            }
        }

        return $lates;
    }
}