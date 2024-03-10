<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Rate;
use App\Models\User;
use App\Models\Report;
use App\Models\RateType;
use App\Models\Attendance;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Auth;

class ReportServices
{
    public function StoreReport($request)
    {
        $validate = $request->validated();
        $new_report = Report::create([
            'user_id' => Auth::id(),
            'content' => $validate->content,
        ]);
        return ResponseHelper::success($new_report, null, 'report created successfully', 200);
    }

    public function RemoveReport($id)
    {
        $remove = Report::findorFail($id)->delete();
        return ResponseHelper::success($remove, null, 'report removed successfully', 200);
    }

    public function MyReports()
    {
        $all = Report::query()
            ->where('user_id', Auth::id())
            ->get()
            ->toArray();
        return ResponseHelper::success($all, null, 'all user reports returned successfully', 200);
    }

    public function AllReports($request)
    {
        $date = $request->date;
        $result = User::with([
            'notes',
            'userInfo',
            'deposits',
            'department',
            'penalties',
            // 'Warnings',
            // 'Deductions',
            // 'Rewards',
            'attendance' => function ($query) use ($date) {
                $query->whereDate('datetime', $date);
            }
        ])->get()->toArray();
        return ResponseHelper::success([
            $result
        ]);
    }

    public function DailyReports($request)
    {
        $branchId = $request->input('branch_id');
        $user = User::where('branch_id', $branchId);
        $today = $user
            ->reports()
            ->whereDate('created_at', now()
                ->format('Y-m-d'))
            ->get()
            ->toArray();
        return ResponseHelper::success($today, null, 'today reports returned successfully', 200);
    }

    public function UserChecks($request)
    {
        $work_time_start = Carbon::parse('09:00:00');
        $work_time_end = Carbon::parse('17:00:00');
        $date_time = Carbon::parse($request->date);
        $checks = Attendance::where('pin', $request->user_id)
            ->whereDate('datetime', $date_time->format('Y-m-d'))->get();
        foreach ($checks as $check) {
            if ($check->status == 0) {
                $enter = Carbon::parse($check->datetime)->format('H:i:s');
                $lateness_in_mins = $work_time_start->diffInMinutes($enter);
                $lateness_in_mins = $lateness_in_mins % 60;
                $lateness_in_hrs = $work_time_start->diffInHours($enter);
            } elseif ($check->status == 1) {
                $out = Carbon::parse($check->datetime)->format('H:i:s');
                $overtime_in_mins = $work_time_end->diffInMinutes($out);
                $overtime_in_mins = $overtime_in_mins % 60;
                $overtime_in_hrs = $work_time_end->diffInHours($out);
            }
        }
        return ResponseHelper::success(
            [
                'late_in_mins' => $lateness_in_mins,
                'late_in_hrs' => $lateness_in_hrs,
                'over_in_mins' => $overtime_in_mins,
                'over_in_hrs' => $overtime_in_hrs,
            ],
            null,
            'user check insNouts returned successfully',
            200
        );
    }

    public function Report($request)
    {
        $date = $request->date;
        $result = User::with([
            'notes',
            'userInfo',
            'deposits',
            'department',
            'penalties',
            // 'Warnings',
            // 'Deductions',
            // 'Rewards',
            'attendance' => function ($query) use ($date) {
                $query->whereDate('datetime', $date);
            }
        ])->find($request->user_id);
        return ResponseHelper::success([
            $result
        ]);
    }

    public function RatesByDate($request)
    {
        $user = Auth::user();
        $date = substr($request->date, 0, 7);
        $result = Rate::where('user_id', $user->id)->whereRaw("SUBSTRING(date, 1, 4) = ?", [$date])
            ->orWhere(function ($query) use ($date) {
                $query->whereRaw("SUBSTRING(date, 1, 4) = ?", [substr($date, 0, 4)])
                    ->whereRaw("SUBSTRING(date, 6, 2) = ?", [substr($date, 5, 2)]);
            })
            ->get();
        $groupedRates = $result->groupBy('rate_type_id');
        $ratesWithPercentage = $groupedRates->map(function ($rates, $key) {
            $rateSum = $rates->sum('rate');
            $totalRateCount = $rates->count();
            $percentage = ($rateSum / $totalRateCount) * 10;
            return [
                'rate_type' => RateType::where('id', $key)->first()->rate_type,
                'percentage' => $percentage,
            ];
        });
        return ResponseHelper::success($ratesWithPercentage->values());
    }
}

