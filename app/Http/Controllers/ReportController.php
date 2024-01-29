<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\ReportRequest;
use App\Models\Attendance;
use App\Models\Note;
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
        $rewards = $user->my_decisions()->where('type', 'reward')->whereDate('dateTime', $date)->get();
        $warnings = $user->my_decisions()->where('type', 'warning')->whereDate('dateTime', $date)->get();
        if ($warnings->count() == 3) {
            $alert = 1;
        } else {
            $alert = null;
        }
        if ($deductions) {
            $penalties = 'Deduction';
        } else {
            $penalties = null;
        }
        $advances = $user->getAdvancesAttribute($date);
        $absence = $user->getUserAbsence($date)->first();
        if ($absence) {
            $result = true;
        } else {
            $result = false;
        }
        $deposits = $user->deposits()->get();
        $notes = Note::query()->where('user_id', $request->user_id)->get();
        $checkIn = Attendance::query()->where('pin', $request->user_id)
            ->whereDate('datetime', $request->date)->where('status', 0)->get();
        if (!$checkIn->isEmpty()) {
            $checkIn = $checkIn[0]->datetime;
        } else { $checkIn == null;}
        $checkOut = Attendance::query()->where('pin', $request->user_id)
            ->whereDate('datetime', $request->date)->where('status', 1)->get();
        if (!$checkOut->isEmpty()) {
            $checkOut = $checkOut[0]->datetime;
        } else { $checkIn == null;}
        return ResponseHelper::success([
            'warnings' => $warnings,
            'alerts' => $alert,
            'penalties' => $penalties,

            'salary' => $salary->salary,
            'overtime' => $overTime,
            'rewards' => $rewards,
            'advances' => $advances,
            'deductions' => $deductions,

            'check in' => $checkIn,
            'check out' => $checkOut,
            'absence' => $result,

            'deposits' => $deposits,
            'notes' => $notes,
        ]);
    }



    
}
