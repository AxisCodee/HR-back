<?php

namespace App\Services;

use App\Models\User;
use App\Helper\ResponseHelper;
use Illuminate\Http\Request;

class DecisionService
{
    public static function user_decisions(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date;
        $type = $request->type;

        $year = null;
        $month = null;

        if (strlen($date) === 4) {
            $year = $date;
        } elseif (strlen($date) === 7) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
        }

        $result = User::query()
            ->where('id', $userId)
            ->with(['my_decisions' => function ($query) use ($year, $month, $type) {
                if ($year && !$month) {
                    $query->whereYear('dateTime', $year);
                } elseif ($year && $month) {
                    $query->whereYear('dateTime', $year)
                        ->whereMonth('dateTime', $month);
                }
                $query->where('type', $type);
            }])
            ->first();

        return $result;
    }





    public static function user_absence(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date;

        $year = null;
        $month = null;

        if (strlen($date) === 4) {
            $year = $date;
        } elseif (strlen($date) === 7) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
        }

        $result = User::query()
            ->where('id', $userId)
            ->with(['absences' => function ($query) use ($year, $month) {
                if ($year && !$month) {
                    $query->whereYear('startDate', $year);
                } elseif ($year && $month) {
                    $query->whereYear('startDate', $year)
                        ->whereMonth('startDate', $month);
                }
                $query->where('type', 'Unjustified');
            }])
            ->first();

        return $result;
    }



}
