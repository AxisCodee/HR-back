<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\PolicyRequest;
use Illuminate\Http\Request;
use App\Models\Policy;

class PolicyController extends Controller
{
    public function show($id)
    {
        $policy = Policy::find($id);
        if (!$policy) {
            return ResponseHelper::error('the policy is not saved yet', null);
        }
        return ResponseHelper::success([$policy], null);
    }
    public function store(PolicyRequest $request)
    {
        $validated = $request->validated();
        $policy = Policy::query()->create($validated);
        return ResponseHelper::success([$policy], null);
    }
    public function update(PolicyRequest $request, $id)
    {
        $validated = $request->validated();
        $policy = Policy::find($id);
        if (!$policy) {
            return ResponseHelper::error('the policy is not saved yet', null);
        }
        $policy->update($validated);
        return ResponseHelper::success($policy, null);
    }
}
