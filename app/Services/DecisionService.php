<?php

namespace App\Services;

use App\Models\User;
use App\Models\Decision;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;

class DecisionService
{
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
        return ResponseHelper::created($created, 'decision created successfully');
    }

    public function RemoveDecision($id)
    {
        $removed = Decision::findOrFail($id)->delete();
        return ResponseHelper::success('Decision deleted successfully');
    }

    public function UpdateDecision($request,$id)
    {
        $validate = $request->validated();
            $edited = Decision::where('id', $id)->firstOrFail();
            $edited->update($validate);
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
}
