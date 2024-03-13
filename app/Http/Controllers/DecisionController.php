<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Services\DecisionService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\DecisionRequest\StoreDecisionRequest;
use App\Http\Requests\DecisionRequest\UpdateDecisionRequest;

class DecisionController extends Controller
{
    /**
     * Add new decision for a user.
     * [DecisionService => none]
     * @param StoreDecisionRequest
     * @return ResponseHelper
     */
    public function new_decision(StoreDecisionRequest $request)
    {
        $new = $request->validated();
        $created = Decision::create($new);
        return ResponseHelper::created($created, 'decision created successfully');
    }

    /**
     * Delete an existing decision.
     * [DecisionService => none]
     * @param Decision
     * @return ResponseHelper
     */
    public function remove_decision($id)
    {
        try {
            $removed = Decision::findOrFail($id)->delete();
            return ResponseHelper::success('Decision deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Edit an existing decision.
     * [DecisionService => none]
     * @param UpdateDecisionRequest
     * @param Decision
     * @return ResponseHelper
     */
    public function edit_decision(UpdateDecisionRequest $request, $id)
    {
        try {
            $validate = $request->validated();
            $edited = Decision::where('id', $id)->firstOrFail();
            $edited->update($validate);
            return ResponseHelper::updated($edited, 'Decision updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get all decisions for all users.
     * [DecisionService => none]
     * @param Request
     * @return ResponseHelper
     */
    public function all_decisions(Request $request)
    {
        $branchId = $request->input('branch_id');
        $all = Decision::query()
            ->with('user_decision')->whereHas('user_decision', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->get()->toArray();
        return ResponseHelper::success($all, null, 'all decisions returned successfully', 200);
    }

    /**
     * Get decisions for the authenticated user.
     * [DecisionService => none]
     * @param none
     * @return ResponseHelper
     */
    public function my_decisions()
    {
        $mine = Decision::query()
            ->where('user_id', Auth::id())
            ->get()->toArray();
        return ResponseHelper::success($mine, null, 'user decisions returned successfully', 200);
    }

    /**
     * Get decisions for a specific user.
     * [DecisionService => user_decisions]
     * @param Request
     * @return ResponseHelper
     */
    public function getUserDecisions(Request $request)
    {
        $result = DecisionService::user_decisions($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }
    }

    /**
     * Get absence times for a specific user.
     * [DecisionService => user_absence]
     * @param Request
     * @return ResponseHelper
     */
    public function getUserAbsence(Request $request)
    {
        $result = DecisionService::user_absence($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }
    }

    //     $user = User::with('my_decisions')->findOrFail($id);
    //     $decisions = $user->my_decisions;
    //     $types = ['reward', 'warning', 'deduction', 'alert', 'penalty'];
    //     $abs = Absences::query()->where('user_id',$id)->get()->toArray();
    //     $groupedDecisions = collect($types)->mapWithKeys(function ($type) use ($decisions) {
    //         return [$type => $decisions->where('type', $type)->values()];
    //     })->all();

    //     extract($groupedDecisions);

    //     return ResponseHelper::success([
    //         'rewards'=>$reward,
    //         'warnings'=>$warning,
    //         'deductions'=>$deduction,
    //         'alerts'=>$alert,
    //         'penalty'=>$penalty,
    //         'absences'=>$abs,
    //         ]
    //         , null, 'user decisions returned successfully', 200);

}
