<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\ReportRequest;
use App\Models\Attendance;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    //store new Report
    public function store(ReportRequest $request)
    {
        $validate = $request->validated();
        $new_report = Report::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);
        return ResponseHelper::success($new_report, null, 'report created successfully', 200);
    }
    //remove existing report by a specific user
    public function remove($id)
    {
        $remove = Report::findorFail($id)->delete();
        return ResponseHelper::success($remove, null, 'report removed successfully', 200);
    }
    //get all user's reports
    public function my_reports()
    {
        $all = Report::query()->where('user_id', Auth::user()->id)->get()->toArray();
        return ResponseHelper::success($all, null, 'all user reports returned successfully', 200);
    }

    //get all reports

    public function all_reports($branchId)
    {
        $user = User::where('branch_id', $branchId);
        $all = $user->reports()->get()->toArray();
        return ResponseHelper::success($all, null, 'all user reports returned successfully', 200);
    }

    //get all reports of today
    public function daily_reports($branchId)
    {
        $user = User::where('branch_id', $branchId);
        $today = $user->reports()->whereDate('created_at', now()->format('Y-m-d'))->get()->toArray();
        return ResponseHelper::success($today, null, 'today reports returned successfully', 200);
    }
    //get CHECK-INs & CHECK-OUTs of a user in a specific day
    public function user_checks(Request $request)
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

    public function report(Request $request)
    {
        $date = $request->date;
        $result = User::with(['notes', 'deposits', 'department', 'attendance' => function ($query) use ($date) {
            $query->whereDate('datetime', $date);
        }])->find($request->user_id);
        return ResponseHelper::success([
            $result
        ]);
    }
}
