<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Helper\ResponseHelper;
use App\Http\Traits\Files;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function index($branchId)
    {

        $contracts = Contract::with('user')->whereHas('user', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId); })->get();

        if ($contracts->isEmpty()) {;
            return ResponseHelper::success([
                'message' => 'there are not any contract'
            ]);
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

        return ResponseHelper::error('error', null);
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
            ->with('user')
            ->where('id', $id)
            ->get()->toArray();

        return ResponseHelper::success($result, null, 'contract:', 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return ResponseHelper::success([
            null, null, 'contract deleted successfully'

        ]);
    }
}
