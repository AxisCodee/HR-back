<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Policy;
use App\Models\RateType;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\DB;

class PolicyServices
{
    public function BranchPolicy($request)
    {
        $policy = Policy::query()->where('branch_id', $request->branch_id)->first();
        $rateTypes = RateType::query()->where('branch_id', $request->branch_id)->get();
        if (!$policy) {
            return ResponseHelper::error('this branch doesnt have policy', null);
        }
        return ResponseHelper::success(["policy" => $policy, "rateTypes" => $rateTypes], null);
    }

    public function StorePolicy($request)
    {
        $validated = $request->toArray();
        $existencePolicy = Policy::where('branch_id', $validated['branch_id'])->exists();
        if ($existencePolicy) {
            return ResponseHelper::error('Already exist.', null);
        }
        return DB::transaction(function () use ($validated) {
            $types = $validated['rate_type'];
            $validated = collect($validated)->except('rate_type')->toArray();
            $branchID = $validated['branch_id'];
            $policy = Policy::query()->create($validated);

            $startTime = Carbon::createFromFormat('h:i A', $policy->work_time['start_time']);
            $endTime = Carbon::createFromFormat('h:i A', $policy->work_time['end_time']);

            $workhours = $startTime->diffInHours($endTime, false);
            $workdays = sizeof($policy->work_time['work_days']) * 4;
            $policy->update(['monthlyhours' => $workhours * $workdays]);

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
    }

    public function UpdatePolicy($request)
    {
        $existencePolicy = Policy::where('branch_id', $request->branch_id)->exists();
        if (!$existencePolicy) {
            return ResponseHelper::error('Not exist, Store it first!.');
        }
        return DB::transaction(function () use ($request) {
            $policy = Policy::query()->where('branch_id', $request->branch_id)->first();
            $types = $request['rate_type'];
            $validated = collect($request)->except('rate_type')->toArray();
            $policy->update($validated);
            $branchID = $validated['branch_id'];
            if ($types) {
                RateType::query()->where('branch_id', $request->branch_id)->delete();
                foreach ($types as $type) {
                    RateType::create(
                        ['branch_id' => $branchID, 'rate_type' => $type],
                        ['branch_id' => $branchID]
                    );
                }
            }
            return ResponseHelper::updated('updated', null);
        });
    }

    public function DeletePolicy($request)
    {
        $existencePolicy = Policy::where('branch_id', $request->branch_id)->exists();
        if (!$existencePolicy) {
            return ResponseHelper::error('Not exist!', null);
        }
        Policy::where('branch_id', $request->branch_id)->delete();
        RateType::query()->where('branch_id', $request->branch_id)->delete();
        return ResponseHelper::success('Deleted', null);
    }
}
