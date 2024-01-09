<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Helper\ResponseHelper;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractRequest $request)
    {
        $contract= Contract::create(
            [
                'path'=>$request->path,
                'startTime'=>$request->startTime,
                'endTime'=>$request->endTime,
                'user_id'=>$request->user_id
            ]
            );
            return ResponseHelper::success([
                'message' => 'Contract created successfully',
                'user' => $contract,
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        //
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
        //
    }
}
