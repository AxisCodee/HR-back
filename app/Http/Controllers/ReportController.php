<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Report;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\ReportRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class ReportController extends Controller
{
    //store new Report
    public function store(ReportRequest $request)
    {
        $validate = $request->validated();
        $new_report = Report::create([
            'user_id' => Auth::user()->id,
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

    public function all_reports()
    {
        $all = Report::query()->get()->toArray();
        return ResponseHelper::success($all, null, 'all user reports returned successfully', 200);
    }


    //get all reports of today
    public function daily_reports()
    {
        $today = Report::whereDate('created_at', now()->format('Y-m-d'))->get()->toArray();
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

    public function reportByDay(Request $request)
    {
        $date = $request->date;
        $user = User::find($request->user_id);
        $salary = $user->userInfo()->select('salary')->first();

        $deductions = $user->getDeductionAttribute($date);
        $overTime = $user->getOverTimeAttribute($date);
        $warnings = $user->my_decisions()->where('type', 'warning')->whereDate('dateTime', $date)->get();
        $advances = $user->getAdvancesAttribute($date);
        // $absences = $user->absences()->whereDate('startDate', $date)->get();
        $deposits=$user->deposits()->get();
        return ResponseHelper::success([
            'warnings' => $warnings,
            'alerts' => 'no alerts',
            'penalties' => 'no penalties',

            'salary' => $salary->salary,
            'overtime' => $overTime,
            'rewards' => '200000',
            'advances' => $advances,
            'deductions' => $deductions,


            'check in' => '09:00 AM',
            'check out' => '05:00 PM',
            'absences' => 'no absences',

            'deposits' => $deposits,
            'notes' => 'no notes',
        ]);
    }
}
