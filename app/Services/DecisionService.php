<?php

namespace App\Services;

use App\Models\User;
use App\Models\Decision;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Carbon\Carbon;

class DecisionService
{
    protected $userTimeService;

    public function __construct(UserTimeService $userTimeService)
    {
        $this->userTimeService = $userTimeService;
    }


    public static function user_decisions(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date;
        $type = $request->type;
        $year = null;
        $month = null;
        if (strlen($date) === 4) {
            $year = $date;
        } elseif (strlen($date) === 7) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
        }
        $result = User::query()
            ->where('id', $userId)
            ->with(['my_decisions' => function ($query) use ($year, $month, $type) {
                if ($year && !$month) {
                    $query->whereYear('dateTime', $year);
                } elseif ($year && $month) {
                    $query->whereYear('dateTime', $year)
                        ->whereMonth('dateTime', $month);
                }
                $query->where('type', $type);
            }])
            ->first();
        return $result;
    }

    public static function user_absence(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date;
        $year = null;
        $month = null;
        if (strlen($date) === 4) {
            $year = $date;
        } elseif (strlen($date) === 7) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
        }
        $result = User::query()
            ->where('id', $userId)
            ->with(['absences' => function ($query) use ($year, $month) {
                if ($year && !$month) {
                    $query->whereYear('startDate', $year);
                } elseif ($year && $month) {
                    $query->whereYear('startDate', $year)
                        ->whereMonth('startDate', $month);
                }
                $query->where('type', 'Unjustified');
            }])
            ->first();
        return $result;
    }

    public function StoreDecision($request)
    {
        $new = $request->validated();
        $created = Decision::create($new);
        return ResponseHelper::created($created, 'Decision created successfully');
    }

    public function RemoveDecision($id)
    {
        Decision::findOrFail($id)->delete();
        return ResponseHelper::success('Decision deleted successfully');
    }

    public function UpdateDecision($request, $id)
    {
        $validate = $request->validated();
        $edited = Decision::where('id', $id)->update($validate);;
        return ResponseHelper::updated($edited, 'Decision updated successfully');
    }

    public function AllDecisions($request)
    {
        $branchId = $request->input('branch_id');
        $all = Decision::query()
            ->with('user_decision')->whereHas('user_decision', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->get()->toArray();
        return ResponseHelper::success($all, null, 'all decisions returned successfully', 200);
    }

    public function selectDecision($request)
    {
        foreach ($request->users as $user) {
            $newDecision = Decision::query()->create(
                [
                    'user_id' => $user,
                    'type' => $request->type,
                    'dateTime' => Carbon::now(),
                    'branch_id' => $request->branch_id,
                    'amount' => $request->amount,
                    'content' => 'aaa'
                ]
            );
            $results[] = $newDecision;
        }
        return $results;
    }

    public function selectDecisionToDelete($request)
    {

        foreach ($request->decisions as $item) {
            $oneDecisions = Decision::find($item);
            if ($oneDecisions == null) {
                return 'one request not found';

            } else {
                $result = $oneDecisions->delete();
            }
        }
        return $result;


    }

    public function getSystemDecisions($request)
    {
        $date = request()->query('date');

        $result = Decision::where('branch_id', $request->branch_id)->whereIn('type', ['alert', 'absence', 'dismiss', 'deduction'])
            ->where('status', 'requested')
            ->with('user_decision:id,first_name,last_name', 'user_decision.userInfo:id,user_id,image', 'user_decision.department:id,user_id,');
        $data = $this->userTimeService->filterDate($result, $date, 'dateTime')->get()->toArray();
        return $data;
    }


    public function AcceptSystemDecisions(Request $request)
    {
        $decisionId = Decision::find($request->id);
        $result = Decision::where('id', $decisionId->id)->update([
            'status' => 'accepted'
        ]);

        return $result;
    }

}
