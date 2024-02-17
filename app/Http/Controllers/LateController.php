<?php

namespace App\Http\Controllers;

use App\Models\Late;
use App\Helper\ResponseHelper;
use App\Http\Requests\StoreLateRequest;
use App\Http\Requests\UpdateLateRequest;
use App\Models\UserInfo;
use App\Models\Decision;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LateController extends Controller
{
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
            $currentMonthYear = Carbon::now()->format('Y-m');

            $result = Late::query()
                ->whereRaw("DATE_FORMAT(lateDate, '%Y-%m') = ?", [$currentMonthYear])
                ->where('type', 'normal')
                ->with('user:id,first_name,department_id', 'user.department', 'user.alert')
                ->get()
                ->toArray();

            return ResponseHelper::success($result, null, 'alerts', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLateRequest $request, Late $late)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Late $late)
    {
        //
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
            $user=User::find($late->user_id);
            Decision::query()->updateOrCreate(
                [
                    'user_id' => $late->user_id,
                    'type' => 'deduction',
                    'salary' => $salary,
                    'dateTime' => Carbon::now(),
                    'fromSystem' => true,
                    'content' => 'Unjustified late',
                    'amount' => $deduction,
                    'branch_d'=> $user->branch_id
                ]
            );
            return ResponseHelper::success($late, 'unjustifiedLate', null);
        });
        return ResponseHelper::error('Error', null);
    }



    public function unjustifiedLate()
    {
        $lates = Late::query()->where('type', 'normal')->where('status', 'waiting')->get();
        return ResponseHelper::success($lates, 'unjustifiedLate', null);
    }
    public function dynamicDecision()
    {
        $lates = Late::query()->where('type', 'null')->where('status', 'waiting')->get();
        foreach ($lates as $late) {
            $this->makeDecision($late->id);
        }
        return ResponseHelper::success(null, 'Decision done successfully', null);
    }
}
