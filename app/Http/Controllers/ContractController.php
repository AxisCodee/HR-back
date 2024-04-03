<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Http\Requests\ContractRequest\StoreContractRequest;
use App\Http\Requests\ContractRequest\UpdateContractRequest;
use App\Helper\ResponseHelper;
use App\Http\Traits\Files;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');
        $contracts = Contract::with('user', 'user.userInfo')
        ->whereHas('user', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })->get();
        if ($contracts->isEmpty()) {
            return ResponseHelper::success([], null, 'no contract', 200);
        } else {
            foreach ($contracts as $contract) {
                $endTime = Carbon::parse($contract['endTime']);
                if ($endTime->diffindays(Carbon::now()) < 0) {
                    $status = 'finished';
                } else {
                    $status = 'active';
                }
                $results[] = $result = [
                    'startDate' => $contract['startTime'],
                    'path' => $contract['path'],
                    'endDate' => $contract['endTime'],
                    'user_id' => $contract['user_id'],
                    'contract_id' => $contract->id,
                    'user' => $contract['user'],
                    'status' => $status,
                ];
            }
            return ResponseHelper::success(
                $results

            );
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $path = Files::saveFile($request);
            $contract = Contract::create(
                [
                    'path' => $path,
                    'startTime' => $request->startTime,
                    'endTime' => $request->endTime,
                    'user_id' => $request->user_id
                ]
            );
            return ResponseHelper::success(
                $contract,
                null,
                'contract',
                200
            );
        });
    }

    /**
     * Display the specified resource.
     */
    // public function show(Contract $contract)
    // {
    //    $result= $contract->with('user')->get();
    //    return ResponseHelper::success([
    //     'message' => 'your contract',
    //     'data' =>  $result,
    // ]);
    // }
    public function show($id)
    {
        $result = Contract::query()
            ->with('user',
            'user.userInfo')
            ->where('user_id', $id)
            ->get()->toArray();
        return ResponseHelper::success($result, null, 'contract:', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, $contract)
    {
        try {
            $contractId = Contract::findOrFail($contract);
            if ($contract) {
                if (Carbon::parse($contractId->endTime) <= Carbon::now()) {
                    return ResponseHelper::error('The Contract must be Valid');
                }
                $validate = $request->validated();
                $path = Files::saveFile($request);
                $contractId->update([
                    'startTime' => $request->startTime ?: $contractId->startTime,
                    'endTime' => $request->endTime ?: $contractId->path,
                    'path' => $path
                ]);
                return ResponseHelper::success($contractId, null, 'contract updated successfully', 200);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return ResponseHelper::success('contract deleted successfully');
    }

    /**
     * Get All Archived contracts.
     */
    public function archivedContracts()
    {
        $contracts = Contract::query()->where('endTime', '<=', Carbon::now())
            ->get()->toArray();
        return ResponseHelper::success($contracts);
    }
}
