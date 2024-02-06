<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\User;
use App\Http\Requests\StoreRateRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Helper\ResponseHelper;
use App\Http\Requests\RateRequest;
use App\Models\RateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Services\RateService;
use Illuminate\Http\Request;
class RateController extends Controller
{


    protected $rateService;

    public function __construct(RateService $rateService)
    {
        $this->rateService = $rateService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {

        // $userRate= $user->evaluatorRates()->get();
        $rate = $user->userRates()->get(['rate', 'evaluator_role'])
            ->toArray();
        if (!$rate) {
            return ResponseHelper::success(null, null, 'there are not any Rate', 200);
        } else {

            return ResponseHelper::success($rate, null, 'userRate', 200);
        }
    }


    public function setRate(RateRequest $request)
    {
        $userId = $request->user_id;
        $rateTypeId = $request->rate_type_id;
        $rate = $request->rate;
        try {
            $result = $this
            ->rateService
            ->setRate($userId, $rateTypeId, $rate);

            return ResponseHelper::success($result, null, 'Rate added successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Rate $rate)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRateRequest $request, Rate $rate)
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rate $rate)
    {
        $rate->delete();
        return ResponseHelper::success(null, null, 'deleted successfully');
    }
    public function showMyRate(User $user)
    {
        $user = User::find(Auth::id());
        $userRate = $user->userRates()->get(['rate', 'evaluator_role'])->toArray();
        if (!$userRate) {
            return ResponseHelper::success(null, null, 'there are not any Rate', 200);
        } else {
        }
        return ResponseHelper::success($userRate, null, 'yourRate', 200);
    }


    public function getRate(Request $request, $id)
    {
        return $this->rateService->getRate($request, $id);
    }

    public function allRates(Request $request)
    {
        $rates = Rate::with(['rateType' => function ($query) use ($request) {
                $query->where('branch_id', $request->branch_id);
            }])
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($items) {
                $evaluatorCount = $items->countBy('evaluator_id');
                return $items->map(function ($item) use ($evaluatorCount) {
                    $item['evaluator_count'] = $evaluatorCount[$item['evaluator_id']];
                    return $item;
                });
            })
            ->toArray();

        return ResponseHelper::success($rates, null, 'rates', 200);
    }

}
