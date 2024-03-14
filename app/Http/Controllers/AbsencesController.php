<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\AbsencesRequest\StoreAbsencesRequest;
use App\Models\User;
use App\Services\AbsenceService;
use Illuminate\Http\Request;

class AbsencesController extends Controller
{
    protected $absenceService;

    public function __construct(AbsenceService $absenceService)
    {
        $this->absenceService = $absenceService;
    }

    public function index(Request $request)
    {
        return $this->absenceService->index($request);
    }

    public function show(User $user)
    {
        try {
            $result = $this->absenceService->show($user);
            return ResponseHelper::success($result, null, 'Absence');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function update(Request $request)
    {
        try {
            $result = $this->absenceService->update($request);
            return ResponseHelper::success($result, null, 'Absence updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function getDailyAbsence(Request $request, $branch)
    {
        $result = $this->absenceService->getDailyAbsence($request, $branch);
        return ResponseHelper::success($result, null, 'daily absence');
    }
    // public function cuurentAbsence(Request $request)
    // {
    //     $all_users = User::query()->where('branch_id', $request->branch_id)
    //     ->with('department', 'userInfo:id,user_id,image')
    //     ->whereNull('deleted_at')->get()->toArray();
    // $usersWithoutAttendance = DB::table('users')
    //                         ->leftJoin('attendances', function ($join)  {
    //                             $join->on('users.pin', '=', 'attendances.pin')
    //                                 ->whereRaw('DATE(attendances.datetime) = ?', Carbon::now()->format('Y-m-d H:i:s'));
    //                         })
    //                         ->whereNull('attendances.pin')
    //                         ->select('users.*')
    //                         ->get()->toArray();

    //         $usersWithStatus = collect( $all_users)
    //         ->map(function ($user) use ($usersWithoutAttendance) {
    //             return array_merge($user, [
    //                 'status' => in_array($user['id'], array_column($usersWithoutAttendance, 'id')) ? 'null' : '1',
    //             ]);
    //         })
    //         ->values()
    //         ->all();

    //     return ResponseHelper::success($usersWithStatus, 'yaaaaaa', null);
    // }

    // to make desicion to absence employee
    // public function makeDecision(Absences $Absence)
    // {
    //     return DB::transaction(function () use ($Absence) {
    //         $Absence->update(
    //             [
    //                 'type' => 'unjustified'
    //             ]
    //         );
    //         $salary = UserInfo::query()->where('user_id', $Absence->user_id)->value('salary');
    //         $salaryInHour = $salary / 208;
    //         $deduction = $salaryInHour * 8;
    //         $user = User::find($Absence->user_id);
    //         Decision::query()->updateOrCreate(
    //             [
    //                 'user_id' => $Absence->user_id,
    //                 'type' => 'deduction',
    //                 'salary' => $salary,
    //                 'dateTime' => Carbon::now(),
    //                 'fromSystem' => true,
    //                 'content' => 'Unjustified absence',
    //                 'amount' => $deduction,
    //                 'branch_id' => $user->branch_id
    //             ]
    //         );
    //         return ResponseHelper::success(null, 'Decision made successfully', null);
    //     });
    //     return ResponseHelper::error('error', null);
    // }

    //to get all users who do not take vacation and absence
    public function unjustifiedAbsence()
    {
        $absence = $this->absenceService->unjustifiedAbsence();
        return ResponseHelper::success($absence, null);
    }
    // if the admin want to make a decision dynamic
    // public function dynamicDecision()
    // {
    //     $absences = Absences::query()->where('type', 'null')->where('status', 'waiting')->get();
    //     foreach ($absences as $absence) {
    //         $this->makeDecision($absence->id);
    //     }
    //     return ResponseHelper::success(null, 'Decision done successfully', null);
    // }

    public function store_absence(StoreAbsencesRequest $request)//store multi
    {
        try {
            $request->validated();
            $results = $this->absenceService->store_absence($request);
            return ResponseHelper::success($results, null, 'Absence added successfully');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e);
        }
    }

    public function storeAbsence(Request $request)//store one
    {
        try {
            //   $request->validated();
            $result = $this->absenceService->storeAbsence($request);
            return ResponseHelper::success($result, null, 'Absence added successfully');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e);
        }
    }

    public function getAbsences($user)
    {
        return $this->absenceService->getAbsences($user);
    }

    public function deleteAbsence($absence)
    {
        $result = $this->absenceService->deleteAbsence($absence);
        return ResponseHelper::success(null, null, $result);
    }


    public function getUserAbsence(Request $request)
    {
        $result = $this->absenceService->user_absence($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }

    }
}

