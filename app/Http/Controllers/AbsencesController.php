<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UpdateAbsencesRequest;
use App\Models\Absences;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Decision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsencesController extends Controller
{
    public function index(Request $request)
    {

        if ($request->has('date')) {
            $dateInput = request()->input('date');
            $year = substr($dateInput, 0, 4);
            $month = substr($dateInput, 5, 2);
        } else {
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
        }
        $user = User::query()->get();

        foreach ($user as $item) {

            $justified = $item->absences()
                ->where('type', 'justified')
                ->whereYear('startDate', $year)
                ->whereMonth('startDate', $month)->count();
            $Unjustified = $item->absences()
                ->where('type', 'Unjustified')
                ->whereYear('startDate', $year)
                ->whereMonth('startDate', $month)
                ->count();



            $results[] = $result =
                [
                    'id' => $item->id,
                    'username' => $item->first_name,
                    'userDepartment' => $item->department,
                    'userUnjustified' => $Unjustified,
                    'userjustified' =>   $justified,
                    'all' => $Unjustified + $justified
                ];
        }
        return ResponseHelper::success($results);
    }

    public function show(User $user)
    {
        $userAbcences = $user->absences()->get('startDate')->toArray();
        return ResponseHelper::success($userAbcences);
    }

    public function update(UpdateAbsencesRequest $request, Absences $absences)
    {
        $result = $absences->update(
            [
                'startDate' => $request->startDate
            ]
        );
        return ResponseHelper::success($result);
    }

    public function getDailyAbsence(Request $request)
    {
        $today = Carbon::now();
        if ($today->eq($request->date)) {
            $this->cuurentAbsence();
        } else {
            $dateInput = request()->input('date');
            $day = substr($dateInput, 8, 2);
            $user = User::query()->get();
            $result = $user->with('absences')
                ->whereDay('startDate', $day)->get();
            return ResponseHelper::success($result);
        }
    }
    public function cuurentAbsence()
    {

        $usersWithoutAttendance = DB::table('users')
            ->leftJoin('attendances', function ($join) {
                $join->on('users.pin', '=', 'attendances.pin')
                    ->whereDate('attendances.datetime', '=', Carbon::now()->format('y,m,d'));
            })
            ->whereNull('attendances.pin')
            ->select('users.*')
            ->get();

        return ResponseHelper::success($usersWithoutAttendance, 'yaaaaaa', null);
    }

    // to make desicion to absence employee
    public function makeDecision(Absences $Absence)
    {
        return DB::transaction(function () use ($Absence) {
            $Absence->update(
                [
                    'type' => 'unjustified'
                ]
            );
            $salary = UserInfo::query()->where('user_id', $Absence->user_id)->value('salary');
            $salaryInHour = $salary / 208;
            $deduction = $salaryInHour * 8;
            Decision::query()->updateOrCreate(
                [
                    'user_id' => $Absence->user_id,
                    'type' => 'deduction',
                    'salary' => $salary,
                    'dateTime' => Carbon::now(),
                    'fromSystem' => true,
                    'content' => 'Unjustified absence',
                    'amount' => $deduction
                ]
            );
            return ResponseHelper::success(null, 'Decision made successfully', null);
        });

        return ResponseHelper::error('error', null);
    }

    //to get all users who don not take vacation and absence
    public function unjustifiedAbsence()
    {
        $absence = Absences::query()->where('type', 'null')->where('status', 'waiting')->get();
        return ResponseHelper::success($absence, 'unjustifiedAbsence', null);
    }
    // if the admin want to make a decision dynamic
    public function dynamicDecision()
    {
        $absences = Absences::query()->where('type', 'null')->where('status', 'waiting')->get();
        foreach ($absences as $absence) {
            $this->makeDecision($absence->id);
        }
        return ResponseHelper::success(null, 'Decision done successfully', null);
    }


    
}
