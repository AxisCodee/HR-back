<?php

namespace App\Services;

use App\Models\Rate;
use App\Models\RateType;
use App\Models\User;
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
                'evaluator_id' => $user->id,
            ]);

            return $result;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Invalid rate type ID');
        }
    }
}
