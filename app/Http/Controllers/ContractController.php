<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Http\Requests\ContractRequest\StoreContractRequest;
use App\Http\Requests\ContractRequest\UpdateContractRequest;
use App\Helper\ResponseHelper;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');
        $contracts = Contract::with('user', 'user.userInfo')
            ->whereHas('user', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })->get();
        if ($contracts->isEmpty()) {
            $results = [];
            return ResponseHelper::success($results);
        } else {
            foreach ($contracts as $contract) {
                $endTime = Carbon::parse($contract['endTime']);
                if ($endTime->gte(Carbon::now())) {
                    $status = 'active';
                    $result = [
                        'startDate' => $contract['startTime'],
                        'path' => $contract['path'],
                        'endDate' => $contract['endTime'],
                        'user_id' => $contract['user_id'],
                        'contract_id' => $contract->id,
                        'user' => $contract['user'],
                        'status' => $status,
                    ];
                    $results[] = $result;
                }
            }
            return ResponseHelper::success($results);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractRequest $request)
    {
        return DB::transaction(function () use ($request) {
            if ($request->has('path')) {
                $path = $this->fileService->upload($request->file('path'), 'file');
            }
            $contract = Contract::create(
                [
                    'path' => $path,
                    'startTime' => Carbon::parse($request->startTime)->format('Y-m-d H:i:s'),
                    'endTime' => $request->endTime,
                    'user_id' => $request->user_id
                ]
            );
            return ResponseHelper::success($contract, null, 'contract');
        });
    }

    /**
     * Display the specified resource.
     */
    public function showContract(Contract $contract)
    {
        $result = $contract->with('user')->get();
        return ResponseHelper::success([
            'message' => 'your contract',
            'data' => $result,
        ]);
    }

    public function show($id)
    {
        $result = Contract::query()
            ->with('user', 'user.userInfo')
            ->where('user_id', $id)
            ->get()->toArray();
        return ResponseHelper::success($result, null, 'contract:', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $contract)
    {
        $contract = Contract::findOrFail($contract);
        if ($contract) {
            if (Carbon::parse($contract->endTime) <= Carbon::now()) {
                return ResponseHelper::error('The Contract must be Valid');
            }
            $path = $contract->path;
            if ($request->has('path')) {
                $path = $this->fileService->update($contract->path, $request->file('path'), 'file');
            }
            $startTime = $contract->startTime;
            if ($request->startTime) {
                $startTime = Carbon::parse($request->startTime)->format('Y-m-d H:i:s');
            }
            $contract->update([
                'startTime' => $startTime,
                'endTime' => $request->endTime ?: $contract->endTime,
                'path' => $path,
            ]);
            return ResponseHelper::success($contract, null, 'contract updated successfully', 200);
        }
        return ResponseHelper::error('The Contract does not exist');
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
    public function archivedContracts(Request $request)
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
                if ($endTime->gt(Carbon::now())) {
                    $status = 'active';
                } else {
                    $status = 'finished';
                }
                $results[] = [
                    'startDate' => $contract['startTime'],
                    'path' => $contract['path'],
                    'endDate' => $contract['endTime'],
                    'user_id' => $contract['user_id'],
                    'contract_id' => $contract->id,
                    'user' => $contract['user'],
                    'status' => $status,
                ];
            }
            return ResponseHelper::success($results);
        }
    }


    public function selectContractToDelete(Request $request)
    {
        foreach ($request->contracts as $request) {
            $oneRequest = Contract::find($request);
            $oneRequest->delete();
        }
        return ResponseHelper::deleted();
    }
}
