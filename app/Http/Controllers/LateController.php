<?php

namespace App\Http\Controllers;

use App\Models\Late;
use App\Helper\ResponseHelper;
use App\Http\Requests\LateRequest\StoreLateRequest;
use App\Http\Requests\LateRequest\UpdateLateRequest;
use App\Models\UserAlert;
use App\Models\UserInfo;
use App\Models\Decision;
use Carbon\Carbon;
use App\Models\User;
use App\Services\LateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LateController extends Controller
{
    protected $lateService;
    public function __construct(LateService $lateService)
    {
        $this->lateService = $lateService;

    }
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function showLate(Request $request)
    {
        try {
            $branchId = $request->branch_id;
            $currentMonthYear = Carbon::now()->format('Y-m');
            $result = Late::query()
                ->whereRaw("DATE_FORMAT(lateDate, '%Y-%m') = ?", [$currentMonthYear])
                ->where('type', 'normal')
                ->with('user:id,first_name,last_name,department_id,specialization', 'user.department', 'user.alert', 'user.userInfo:id,image')
                ->whereHas('user', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->get()
                ->toArray();
            return ResponseHelper::success($result, null, 'alerts', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error(null, $e->getMessage(), 500);
        }
    }

    public function update(StoreLateRequest $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function rejectAlert(Request $request)
    {
        $late = Late::find($request->alert_id);
        if (!$late) {
            return ResponseHelper::error('Alert not found');
        }
        $late->update([
            'type' => 'justified'
        ]);
        return ResponseHelper::success([], 'Alert rejected successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function acceptAlert(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $late = Late::find($request->alert_id);
                if (!$late) {
                    throw new \Exception('Alert not found');
                }
                $user_id = $late->user_id;
                $user = User::findOrFail($user_id);
                if ($late->type === 'normal') {
                    $late->update([
                        'type' => 'Unjustified'
                    ]);
                    $alert = Decision::create([
                        'user_id' => $user_id,
                        'branch_id' => $user->branch_id,
                        'content' => 'alert for late',
                        'type' => 'warning',
                        'dateTime' => Carbon::now()->format('Y-m-d')
                    ]);
                    $alert = UserAlert::create([
                        'user_id' => $late->user_id,
                        'alert' => 1,
                        'date' => Carbon::now()->format('Y-m-d')
                    ]);
                }
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('Error accepting alert: ' . $e->getMessage());
        }
        return ResponseHelper::success('Alert accepted successfully');
    }

    public function makeDecision(Late $late)
    {
        return DB::transaction(function () use ($late) {
            $late->update(
                [
                    'type' => 'unjustified'
                ]
            );
            $salary = UserInfo::query()->where('user_id', $late->user_id)->value('salary');
            $salaryInHour = $salary / 208;
            $HourNum = $late->hours_num;
            $deduction = $salaryInHour * $HourNum;
            $user = User::find($late->user_id);
            Decision::query()->updateOrCreate(
                [
                    'user_id' => $late->user_id,
                    'type' => 'deduction',
                    'salary' => $salary,
                    'dateTime' => Carbon::now(),
                    'fromSystem' => true,
                    'content' => 'Unjustified late',
                    'amount' => $deduction,
                    'branch_d' => $user->branch_id
                ]
            );
            return ResponseHelper::success($late, 'unjustifiedLate', null);
        });
    }


    public function unjustifiedLate()
    {
        $lates = Late::query()->where('type', 'normal')
            ->where('status', 'waiting')->get();
        return ResponseHelper::success($lates, 'unjustifiedLate', null);
    }

    public function dynamicDecision()
    {
        $lates = Late::query()->where('type', 'null')
            ->where('status', 'waiting')->get();
        foreach ($lates as $late) {
            $this->makeDecision($late->id);
        }
        return ResponseHelper::success(null, 'Decision done successfully', null);
    }





    public function getUserLates(Request $request)
    {
        $result = $this->lateService->userLates($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }

    }


    public function lateTypes(Request $request)
    {
        $validate = $request->validate([
            'user_id'=> ['required','exists:users,id','integer'],
        ]);

        $late = $this->lateService->lateTypes($request);

        return ResponseHelper::success(
            $late,null);
    }


    public function allUserLates(Request $request)
    {
        $validate = $request->validate([
            'user_id'=> ['required','exists:users,id','integer'],
        ]);

        $late = $this->lateService->allUserLates($request);

        return ResponseHelper::success(
            $late,null);
    }

}
