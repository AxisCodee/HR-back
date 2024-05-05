<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Services\DecisionService;
use App\Http\Requests\DecisionRequest\StoreDecisionRequest;
use App\Http\Requests\DecisionRequest\UpdateDecisionRequest;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function new_decision(StoreDecisionRequest $request)
    {
        return $this->decisionService->StoreDecision($request);
    }

    public function addDecisions(Request $request)
    {
        $result = $this->decisionService->selectDecision($request);
        return ResponseHelper::success($result, null, 'Absence added successfully');
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
        return $this->decisionService->UpdateDecision($request, $id);
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
            return ResponseHelper::success($result);
        } else {
            return ResponseHelper::error('No results found', 404);
        }
    }

    public function selectDecisionToDelete(Request $request)
    {
        $this->decisionService->selectDecisionToDelete($request);
        return ResponseHelper::deleted();

    }


    public function systemDecision(Request $request)
    {
        $result = $this->decisionService->getSystemDecisions($request);
        return ResponseHelper::success($result);
    }

    public function AcceptSystemDecisions(Request $request)
    {
        $result = $this->decisionService->AcceptSystemDecisions($request);
        return ResponseHelper::success($result);
    }

}
