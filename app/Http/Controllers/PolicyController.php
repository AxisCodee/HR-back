<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\PolicyRequest\StorePolicyRequest;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\RateType;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    public function show(Request $request)
    {
        try {
            $policy = Policy::query()->where('branch_id', $request->branch_id)->first();
            $rateTypes = RateType::query()->where('branch_id', $request->branch_id)->get();
            if (!$policy) {
                return ResponseHelper::error('this branch doesnt have policy', null);
            }
            return ResponseHelper::success(["policy" => $policy, "rateTypes" => $rateTypes], null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function store(StorePolicyRequest $request)
    {
        try {
            $validated = $request->validated();
            $existencePolicy = Policy::where('branch_id', $validated['branch_id'])->exists();
            if ($existencePolicy) {
                return ResponseHelper::error('Already exist.', null);
            }
            return DB::transaction(function () use ($validated) {
                $types = $validated['rate_type'];
                $validated = collect($validated)->except('rate_type')->toArray();
                $branchID = $validated['branch_id'];
                $policy = Policy::query()->create($validated);
                foreach ($types as $type) {
                    RateType::query()->create(
                        [
                            'branch_id' => $branchID,
                            'rate_type' => $type
                        ]
                    );
                }
                return ResponseHelper::success([$policy], null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function update(Request $request)
    {
        try {
            $existencePolicy = Policy::where('branch_id', $request->branch_id)->exists();
            if (!$existencePolicy) {
                return ResponseHelper::error('Not exist, Store it first!.', null);
            }
            return DB::transaction(function () use ($request) {
                $policy = Policy::query()->where('branch_id', $request->branch_id)->first();
                $types = $request['rate_type'];
                $validated = collect($request)->except('rate_type')->toArray();
                $policy->update($validated);
                $branchID = $validated['branch_id'];
                if ($types) {
                    RateType::query()->where('branch_id', $request->branch_id)->pluck('rate_type')->toArray();
                    foreach ($types as $type) {
                        RateType::updateOrCreate(
                            ['branch_id' => $branchID, 'rate_type' => $type],
                            ['branch_id' => $branchID]
                        );
                    }
                }
                return ResponseHelper::updated('updated', null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }

    }
    public function destroy(Request $request)
    {
        try {
            $policy = Policy::where('branch_id', $request->branch_id)->first();
            $policy->delete();
            return ResponseHelper::success('Deleted successfully', null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
