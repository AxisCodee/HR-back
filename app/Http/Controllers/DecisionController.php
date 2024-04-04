<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Services\DecisionService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\DecisionRequest\StoreDecisionRequest;
use App\Http\Requests\DecisionRequest\UpdateDecisionRequest;
use Google\Service\Docs\Response;

class DecisionController extends Controller
{
    protected $decisionService;

    public function __construct(DecisionService $decisionService)
    {
        $this->decisionService = $decisionService;
    }


    /**
     * Add new decision for a user.
     * [DecisionService => StoreDecision]
     * @param StoreDecisionRequest
     * @return DecisionService
     */
    public function new_decision(StoreDecisionRequest $request)
    {
        try {
            return $this->decisionService->StoreDecision($request);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }

    }
    public function addDecisions(Request $request)
    {
        try {
            $result= $this->decisionService->selectDecision($request);
            return ResponseHelper::success($result, null, 'Absence added successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }

    }

    /**
     * Delete an existing decision.
     * [DecisionService => RemoveDecision]
     * @param Decision
     * @return DecisionService
     */
    public function remove_decision($id)
    {
        try {
            return $this->decisionService->RemoveDecision($id);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Edit an existing decision.
     * [DecisionService => UpdateDecision]
     * @param UpdateDecisionRequest
     * @param Decision
     * @return decisionService
     */
    public function edit_decision(UpdateDecisionRequest $request, $id)
    {
        try {
            return $this->decisionService->UpdateDecision($request,$id);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get all decisions for all users.
     * [DecisionService => AllDecisions]
     * @param Request
     * @return decisionService
     */
    public function all_decisions(Request $request)
    {
        try {
            return $this->decisionService->AllDecisions($request);
        } catch (\Exception $e) {
        return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
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
    public function selectDecisionToDelete(Request $request)
    {
        {
            try {
                $result= $this->decisionService->selectDecisionToDelete($request);
                return ResponseHelper::deleted();
            } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
            }
        }
    }


    /**
     * Get absence times for a specific user.
     * [DecisionService => user_absence]
     * @param Request
     * @return ResponseHelper
     */
    // public function getUserAbsence(Request $request)
    // {
    //     $result = DecisionService::user_absence($request);
    //     if ($result) {
    //         return ResponseHelper::success($result, null);
    //     } else {
    //         return ResponseHelper::error('No results found', 404);
    //     }
    // }

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

    public function systemDecision(){
        $result = $this->decisionService->getSystemDecisions();
         return ResponseHelper::success($result);
    }

    public function AcceptSystemDecisions(Request $request){
        $result = $this->decisionService->AcceptSystemDecisions($request);
         return ResponseHelper::success($result);
    }

}
