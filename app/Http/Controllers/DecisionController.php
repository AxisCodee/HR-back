<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Decision;
use App\Models\User;
use App\Http\Requests\DecisionRequest;
use App\Models\Absences;
use App\Models\Late;
use App\Models\Rate;
use App\Models\UserInfo;
use App\Services\UserServices;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\DecisionService;

class DecisionController extends Controller
{
    protected $userServices;

    //add new decision for a user
    public function new_decision(DecisionRequest $request)
    {
        $new = $request->validated();
        $created = Decision::create($new);
        return ResponseHelper::created($created, 'decision created successfully');
    }
    //delete an exisiting decision
    public function remove_decision($id)
    {
        try {
            $removed = Decision::findOrFail($id)->delete();
            return ResponseHelper::success('Decision deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    //edit an exisiting decision
    public function edit_decision(DecisionRequest $request, $id)
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
    //get all decisions for all users
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
    //get decisions for the current user
    public function my_decisions()
    {
        $mine = Decision::query()
            ->where('user_id', Auth::id())
            ->get()->toArray();
        return ResponseHelper::success($mine, null, 'user decisions returned successfully', 200);
    }

    public function getUserDecisions(Request $request)
    {
        $result = DecisionService::user_decisions($request);
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
