<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\RateRequest\StoreRateTypeRequest;
use App\Models\RateType;
use Illuminate\Http\Request;

class RateTypeController extends Controller
{
    public function show($id) //show types for branch
    {
        try {
            $types = RateType::query()->where('branch_id', $id)->get()->toArray();
            if (!$types) {
                return ResponseHelper::error('branch doesnt have Rate Types', null);
            }
            return ResponseHelper::success($types, null, 'RateType', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }

    }

    public function store(StoreRateTypeRequest $request)
    {
        try {
            $validated = $request->validated();
            $rateType = RateType::query()->create($validated);
            return ResponseHelper::success($rateType, null, 'RateType', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }

    }

    public function update(Request $request, $id)
    {
        try {
            $rateType = RateType::query()->findOrFail($id)->update(
                ['rate_type' => $request->rate_type]
            );
            if ($rateType) {
                return ResponseHelper::updated(null, 'Updated', 200);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }

    }


    public function getRateType($id)
    {
        try {
            $result = RateType::where('branch_id', $id)->get()->toArray();
            return ResponseHelper::success($result, null, 'rateType', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

}
