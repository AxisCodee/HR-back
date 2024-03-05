<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;

class AbsenceService
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');
        if ($request->has('date')) {
            $dateInput = request()->input('date');
            $year = substr($dateInput, 0, 4);
            $month = substr($dateInput, 5, 2);
        } else {
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
        }
        $user = User::query()->where('branch_id', $branchId)->with('userInfo')->get();

        $results = [];
        foreach ($user as $item) {
            $justified = $item->absences()
                ->where('type', 'justified')
                ->whereYear('startDate', $year)
                ->whereMonth('startDate', $month)->count();

            $unjustified = $item->absences()
                ->where('type', 'Unjustified')
                ->whereYear('startDate', $year)
                ->whereMonth('startDate', $month)
                ->count();
            $results[] = [
                'id' => $item->id,
                'username' => $item->first_name,
                'lastname' => $item->last_name,
                'userDepartment' => $item->department,
                'userUnjustified' => $unjustified,
                'userJustified' => $justified,
                'all' => $unjustified + $justified,
                'userinfo' => $item->userInfo
            ];
        }
        return ResponseHelper::success($results);
    }
}
