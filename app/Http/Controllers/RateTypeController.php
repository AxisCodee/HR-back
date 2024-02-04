<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\RateTypeRequest;
use App\Models\RateType;
use Illuminate\Http\Request;

class RateTypeController extends Controller
{
    public function show($id) //show types for branch
    {
        $types = RateType::query()->where('branch_id', $id)->get();
        if (!$types) {
            return ResponseHelper::error('branch doesnt have Rate Types', null);
        }
        return ResponseHelper::success($types, null, 'RateType', 200);
    }

    public function store(RateTypeRequest $request)
    {
        $validated = $request->validated();
        $rateType = RateType::query()->create($validated);
        return ResponseHelper::success($rateType, null, 'RateType', 200);
    }

    public function update(Request $request, $id)
    {
        $rateType = RateType::query()->find($id)->update(
            ['rate_type'   => $request->rate_type]
        );
        return ResponseHelper::updated(null, 'Updated', 200);
    }
}