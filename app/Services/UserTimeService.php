<?php

namespace App\Services;

use App\Models\Date;
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



    public function checkDate($lates, $date)
    {
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);

            if ($day) {
                $lates->whereDate('datetime', $date);
            } elseif ($month) {
                $lates->whereYear('datetime', $year)
                    ->whereMonth('datetime', $month);
            } else {
                $lates->whereYear('datetime', $year);
            }
        }

        return $lates;
    }

    public function checkAbsenceTimeDate($lates, $date)
    {
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);

            if ($day) {
                $lates->whereDate('startDate', $date);
            } elseif ($month) {
                $lates->whereYear('startDate', $year)
                    ->whereMonth('startDate', $month);
            } else {
                $lates->whereYear('startDate', $year);
            }
        }

        return $lates;
    }


    public function checkPercentageTimeDate($lates, $date)
    {
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);

            if ($day) {
                $lates->whereDate('date', $date);
            } elseif ($month) {
                $lates->whereYear('date', $year)
                    ->whereMonth('date', $month);
            } else {
                $lates->whereYear('date', $year);
            }
        }

        return $lates;
    }


    public function checkTimeDates($lates, $date)
{
    if ($date) {
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);
        $day = substr($date, 8, 2);

        if ($day && $month && $year) {
            $lates->whereYear('dateTime', $year)
                ->whereMonth('dateTime', $month)
                ->whereDay('dateTime', $day);
        } elseif ($month && $year) {
            $lates->whereYear('dateTime', $year)
                ->whereMonth('dateTime', $month);
        } elseif ($year) {
            $lates->whereYear('dateTime', $year);
        }
    }

    return $lates;
}

}
