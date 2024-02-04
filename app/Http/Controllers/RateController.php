<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\User;
use App\Http\Requests\StoreRateRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Helper\ResponseHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RateController extends Controller
{
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

    /**
     * Store a newly created resource in storage.
     */
    public function setRate(StoreRateRequest $request)
{
    $user = User::find(Auth::id());

    try {
        $result = Rate::query()->create([
            'user_id' => $request->user_id,
            'rate_type_id' => $request->rate_type_id,
            'rate' => $request->rate,
            'evaluator_id' => $user->id,
        ]);

        return ResponseHelper::success($result, null, 'rate added successfully', 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return ResponseHelper::success(null, null, 'not found', 200);
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
}
