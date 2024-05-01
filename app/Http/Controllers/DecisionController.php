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

            return $this->decisionService->StoreDecision($request);


    }
    public function addDecisions(Request $request)
    {
            $result= $this->decisionService->selectDecision($request);
            return ResponseHelper::success($result, null, 'Decision added successfully');


    }

    /**
     * Delete an existing decision.
     * [DecisionService => RemoveDecision]
     * @param Decision
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function remove_decision($id)
    {

            return $this->decisionService->RemoveDecision($id);

    }

    /**
     * Edit an existing decision.
     * [DecisionService => UpdateDecision]
     * @param UpdateDecisionRequest
     * @param Decision
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit_decision(UpdateDecisionRequest $request, $id)
    {
            return $this->decisionService->UpdateDecision($request,$id);

    }

    /**
     * Get all decisions for all users.
     * [DecisionService => AllDecisions]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function all_decisions(Request $request)
    {
            return $this->decisionService->AllDecisions($request);

    }

    /**
     * Get decisions for a specific user.
     * [DecisionService => user_decisions]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
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
                $result= $this->decisionService->selectDecisionToDelete($request);
                return ResponseHelper::deleted();

        }
    }


    /**
     * Get absence times for a specific user.
     * [DecisionService => user_absence]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
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
