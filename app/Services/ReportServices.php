<?php

namespace App\Services;

use App\Models\Date;
use App\Models\Late;
use App\Models\Policy;
use Carbon\Carbon;
use App\Models\Rate;
use App\Models\User;
use App\Models\Report;
use App\Models\RateType;
use App\Models\Attendance;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\String\u;

class ReportServices
{
    protected $userTimeService;

    public function __construct(UserTimeService $userTimeService)
    {
        $this->userTimeService = $userTimeService;
    }

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
            'alerts',
            'attendance' => function ($query) use ($date) {
                $query->whereDate('datetime', $date);
            }
        ])->get()->toArray();
        return ResponseHelper::success($result);
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
            'deductions',
            'rewards',
            'advances',
            'warnings',
            'totalLates',
            // 'overTimes',
            'alerts',
            // 'Warnings',
            // 'Deductions',
            // 'Rewards',
            'attendance' => function ($query) use ($date) {
                $query->whereDate('datetime', $date);
            }
        ])->findOrFail($request->user_id);
        if (strlen($date) == 4) {
            $checkInDetails = $this->checkDetails($date, $request->user_id, '0');
            $checkOutDetails = $this->checkDetails($date, $request->user_id, '1');
        }
        if (strlen($date) == 7) {
            $checkInDetails = [];
            $checkOutDetails = [];
        }
        if (strlen($date) == 10) {
            $checkInDetails = $result->attendance->where('status', 0)->first();
            $checkOutDetails = $result->attendance->where('status', 1)->first();
        }
        $result['checkInDetails'] = $checkInDetails;
        $result['checkOutDetails'] = $checkOutDetails;
        return ResponseHelper::success([
            $result,
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

    public function checkDetails($date, $user_pin, $status)
    {
        if (strlen($date) == 4) {
            $allMonths = array_fill_keys(range(1, 12), 0);
            for ($i = 1; $i <= 12; $i++) {
                $year = $date . '-0' . $i;
                $result = $this->getUserChecksPercentage($user_pin, $year, 'Y-m', $status);
                $allMonths[$i] = $result;
            }
            return $allMonths;
        }
        if (strlen($date) == 7) {
            $allMonths = array_fill_keys(range(1, 12), 0);
            $month = date('n', strtotime($date));
            $monthlyPercentages = $this->getUserChecksPercentage($user_pin, $date, 'Y-m', $status);
            $allMonths[$month] = $monthlyPercentages;
            return $allMonths;
        }
        return false;
    }


    public function getUserChecksPercentage($user_pin, $date, $format, $status)
    {
        $dateFormat = $format === 'Y-m' ? "%Y-%m" : "%Y";
        $checks = Attendance::query()
            ->where('pin', $user_pin)
            ->where('status', $status)
            ->whereRaw('DATE_FORMAT(datetime, ?) = ?', [$dateFormat, $date])
            ->count();
        $workDays = $this->workDays($dateFormat, $date);
        if ($workDays == 0) {
            return 0;
        }
        return round((($checks * 100) / $workDays));
    }

    public function monthlyCheckOut($user_id, $dateTime)//out late
    {
        $allMonths = array_fill_keys(range(1, 12), 0);
        $dateFormat = "%Y-%m";
        $user = User::query()->findOrFail($user_id);
        $policy = Policy::query()->where('branch_id', $user->branch_id)->first();
        $companyEndTime = $policy->work_time['end_time'];
        $companyEndTime24 = date("H:i", strtotime($companyEndTime));
        $checks = Attendance::query()
            ->where('pin', $user->pin)
            ->where('status', '1')
            ->whereRaw('DATE_FORMAT(datetime, ?) = ?', [$dateFormat, $dateTime])
            ->whereRaw('TIME(datetime) < ?', [$companyEndTime24])
            ->count();
        $workDays = $this->workDays($dateFormat, $dateTime);
        if ($workDays == 0) {
            return $allMonths;
        }
        $month = date('n', strtotime($dateTime));
        $result = round((($checks * 100) / $workDays));
        $allMonths  [$month] = $result;
        return $allMonths;
    }

    public function monthlyCheckIn($user_id, $dateTime)//in late
    {
        $allMonths = array_fill_keys(range(1, 12), 0);
        $month = date('n', strtotime($dateTime));
        $dateFormat = "%Y-%m";
        $delays = Late::query()->where('user_id', $user_id)
            ->whereRaw('DATE_FORMAT(lateDate, ?) = ?', [$dateFormat, $dateTime])
            ->whereNull('end')
            ->count();
        $workDays = $this->workDays($dateFormat, $dateTime);
        if ($workDays == 0) {
            return $allMonths;
        }
        $result = round((($delays * 100) / $workDays));
        $allMonths  [$month] = $result;
        return $allMonths;
    }

    public function workDays($dateFormat, $date)
    {
        return Date::query()->whereRaw('DATE_FORMAT(date, ?) = ?', [$dateFormat, $date])
            ->count();
    }
}

