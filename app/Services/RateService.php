<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\Rate;
use App\Models\RateType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RateService
{
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
}
