<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Department;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Helper\ResponseHelper;
use App\Http\Traits\Files;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contracts= Contract::with('user')->get();
        foreach($contracts as $contract){
        $endTime=Carbon::parse($contract['endTime']);
        if($endTime->diffindays(Carbon::now())<0)
        {
            $status='finished';

        }
        else
        {
            $status='active';
        }


        $results[]=$result=[
            'startDate'=>$contract['startTime'],
            'path'=>$contract['path'],
            'endDate'=>$contract['endTime'],
            'user_id'=>$contract['user_id'],
            'contract_id'=>$contract->id,
            'user'=>$contract['user'],
            'status'=>$status,
        ];
    }


        return ResponseHelper::success([
            'message' => 'all Contract',
            'data' =>   $results,
        ]);

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractRequest $request)
    {
        $path = Files::saveFile($request);


        $contract= Contract::create(
            [
                'path'=>$path,
                'startTime'=>$request->startTime,
                'endTime'=>$request->endTime,
                'user_id'=>$request->user_id
            ]
            );


            return ResponseHelper::success([
                'message' => 'Contract created successfully',
                'data' =>  $contract,
            ]);
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
   public function show( $res)
    {
       $result= Contract::query()->with('user')
       ->get();

       return ResponseHelper::success([
        'message' => 'contract:',
        'data' =>  $result,
    ]);
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
            'message' => 'contract deleted successfully',

        ]);
    }
}
