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
        $branchId = $request->input('branch_id');
        if ($request->has('date')) {
            $dateInput = request()->input('date');
            $year = substr($dateInput, 0, 4);

            $month = substr($dateInput, 5, 2);
        } else {
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
        }
        $user = User::query()->where('branch_id', $branchId)->get();

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
                    'userjustified' => $justified,
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

    public function update(Request $request)
    {
        try {
            $result = Absences::query()
                ->where('id', $request->id)
                ->update(
                    [
                        'startDate' => $request->startDate,
                        'type' => $request->type
                    ]
                );
            return ResponseHelper::success('updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function getDailyAbsence(Request $request, $branch)
    {
        $today = Carbon::now();
        if ($today->eq($request->date)) {
            $this->cuurentAbsence();
        } else {
            $dateInput = request()->input('date');
            $day = substr($dateInput, 8, 2);
            $user = User::query()->where('branch_id', $branch)->get();
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
            $user = User::find($Absence->user_id);
            Decision::query()->updateOrCreate(
                [
                    'user_id' => $Absence->user_id,
                    'type' => 'deduction',
                    'salary' => $salary,
                    'dateTime' => Carbon::now(),
                    'fromSystem' => true,
                    'content' => 'Unjustified absence',
                    'amount' => $deduction,
                    'branch_id' => $user->branch_id
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

    public function store_absence(Request $request)
    {
        foreach ($request->absence as $item) {
            $new_abs = Absences::create([
                'type' => $item['type'],
                'user_id' => $item['user_id'],
                'startDate' => $item['date'],
            ]);
            $results[] = $new_abs;
        }
        return ResponseHelper::success($results, null, 'Absence added successfully');
    }
    public function storeAbsence(Request $request)
    {
        $new_abs = Absences::create([
            'type' => $request->type,
            'user_id' => $request->user_id,
            'startDate' => $request->date,
        ]);
        return ResponseHelper::success($new_abs, null, 'Absence added successfully');
    }
    public function getAbsences($user)
    {
        $absences = Absences::where('user_id', $user)->get();
        $groupedAbsences = $absences->groupBy('type')->toArray();
        return ResponseHelper::success([
            'justified' => $groupedAbsences['justified'] ?? [],
            'unjustified' => $groupedAbsences['Unjustified'] ?? [],
        ], null, 'Absences returned successfully');
    }

    public function deleteAbsence($absence)
    {
        $result = Absences::find($absence);

        if (!$result) {
            return ResponseHelper::error('Absence not found', 404);
        }

        $result->update([ 
            'type' => 'null'
        ]);

        return ResponseHelper::success([], null, 'Absence deleted successfully', 200);
    }
}
