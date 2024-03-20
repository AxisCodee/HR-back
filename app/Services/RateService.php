<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Rate;
use App\Models\User;
use App\Models\RateType;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RateService
{
    public function UserRates($user)
    {
        $rate = $user->userRates()->get(['rate', 'evaluator_role'])
            ->toArray();
        if (!$rate) {
            return ResponseHelper::success(null, null, 'there are not any Rate', 200);
        } else {

            return ResponseHelper::success($rate, null, 'userRate', 200);
        }
    }

    public function setRate($userId, $rateTypeId, $rate)
    {
        $user = User::find($userId);
        try {
            $rateType = RateType::findOrFail($rateTypeId);

            $result = Rate::query()->create([
                'user_id' => $userId,
                'rate_type_id' => $rateTypeId,
                'rate' => $rate,
                'date' => Carbon::now()->format('Y-m-d'),
                'evaluator_id' => auth()->user()->id,
            ]);
            return $result;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Invalid rate type ID');
        }
    }

    public function getRate($request, $id)
    {
        try {
            $date = $request->input('date');
            $user = User::with('department')
                ->with(['userRates' => function ($query) use ($date) {
                    $query->whereDate('date', '=', $date)
                        ->with(['rateType', 'evaluators' => function ($query) {
                            $query->with('department');
                        }]);
                }])
                ->findOrFail($id);
            if (empty($user->userRates)) {
                return ResponseHelper::success([], null, 'No rates found', 200);
            }
            return ResponseHelper::success($user, null, 'User rates', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function UpdateRate($request, $rate)
    {
        $user = User::find(Auth::id());
        $result = $rate->update(
            [
                'user_id' => $request->user_id,
                'rate' => $request->rate,
                'type' => $request->type,
                'evaluator_id' => $user->id,
                'evaluator_role' => $user->role
            ]
        );
        return ResponseHelper::success($result, null, 'your rate updated successfully', 200);
    }

    public function Delete($rate)
    {
        $rate->delete();
        return ResponseHelper::success(null, null, 'deleted successfully');
    }

    public function MyRate($user)
    {
        $user = User::find(Auth::id());
        $userRate = $user->userRates()->get(['rate', 'evaluator_role'])->toArray();
        if (!$userRate) {
            return ResponseHelper::success(null, null, 'there are not any Rate', 200);
        } else {
        }
        return ResponseHelper::success($userRate, null, 'yourRate', 200);
    }

    public function allRates($request)
    {
        $rates = Rate::with(['rateType' => function ($query) use ($request) {
            $query->where('branch_id', $request->branch_id);
        }])
            ->get()
            ->groupBy('date')
            ->flatMap(function ($items) {
                $evaluatorCount = $items->countBy('evaluator_id');
                $result = [];
                foreach ($items as $item) {
                    $itemData = $item->toArray();
                    $itemData['evaluator_count'] = $evaluatorCount[$item->evaluator_id];
                    $result[] = $itemData;
                }
                return $result;
            })
            ->toArray();

        return ResponseHelper::success($rates, null, 'rates', 200);
    }

    public function DateRate($request, $date)
    {
        $result = RateType::with(['rate' => function ($query) use ($date) {
            $query->whereDate('date', $date);
        }, 'rate.user'])->get()->toArray();
        return ResponseHelper::success($result, null, 'userRates', 200);
    }

    public function UserRateType($request, $date)
    {
        $result = RateType::with(['rate' => function ($query) use ($date) {
            $query->whereDate('date', $date);
        }, 'rate.users'])
            ->whereHas('rate.users', function ($query) use ($request) {
                $query->where('id', $request->user_id);
            })
            ->get()
            ->toArray();
        return ResponseHelper::success($result, null, 'userRates', 200);
    }
    
}
