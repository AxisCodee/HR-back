<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $results= Request::query()
        ->with('users')
        ->get()->toArray();
        return ResponseHelper::success([
            'message' => 'All contract',
            'data' =>   $results,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestRequest $request)
    {
        $requests=Request::query()
        ->create(
            [
             'user_id'=>1,
             'title'=>$request->title,
             'type'=>$request->type,
             'description'=>$request->description,
             'status'=>'waiting'
            ]);
            return ResponseHelper::created($requests,'request created successfully');


    }

    /**
     * Display the specified resource.
     */
    // public function show(Request $request)
    // {
    //     $result=$request->get();
    //     return ResponseHelper::success($result,'your request');

    // }

    public function show(Request $request)
    {
        $result=Request::query()
        ->where('user_id',Auth::user()->id)
        ->get()
        ->toArray();
        return ResponseHelper::success($result,'your request');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestRequest $request, Request $requests)
    {
        if($requests->status = 'waiting')
        {

        $result=$requests->update(
            [
            'title'=>$request->title,
             'type'=>$request->type,
             'description'=>$request->description
            ]);
            $results=$requests->save();


            return ResponseHelper::updated($results,'request updated successfully');
    }
    else
    {
        return ResponseHelper::success([
            'message' => 'you can not delete this request',
        ]);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $requests)
    {
        if($requests->status = 'waiting')
        {
        $requests->delete();

        return ResponseHelper::deleted([
            'message' => 'request deleted successfully'

        ]);

    }
    else
    {
        return ResponseHelper::error([
            'message' => 'you can not delete this request',
        ],null,'error', 403);
    }
}

public function accepteRequest(Request $request)
{
    $request->update(
        [
            'status'=>'accepted'
        ]
        );
        return ResponseHelper::updated([
            'message' => 'request accepted successfully',
        ]);

}
public function rejectRequest(Request $request)
{
    $request->update(
        [
            'status'=>'rejected'
        ]
        );
        return ResponseHelper::success([
            'message' => 'request rejected successfully',
        ]);

}

public function addComplaint (Request $request)
{
    $complaint=Request::query()->create(
        [
            'user_id'=>Auth::id(),
            'type'=>'complaint',
            'description'=>$request->description

        ] );
        return ResponseHelper::created($complaint,'request created successfully');

}
public function getComplaints()
{
    $result=Request::query()
    ->where('type','complaint')
    ->get()->toArray();
    return ResponseHelper::success($result,'your request');
}

}
