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
        $results= Request::query()->with('users')->get();
        return ResponseHelper::success([
            'message' => 'all Contract',
            'data' =>   $results,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestRequest $request)
    {
        $requests=Request::query()->create(
            [
             'user_id'=>Auth::id(),
             'type'=>$request->type,
             'description'=>$request->description
            ]);
            return ResponseHelper::created($requests,'request created successfully');


    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $result=$request->get();
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
             'user_id'=>Auth::id(),
             'type'=>$request->type,
             'description'=>$request->description
            ]);
            return ResponseHelper::created($result,'request updated successfully');
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

        return ResponseHelper::success([
            'message' => 'request deleted successfully',

        ]);

    }
    else
    {
        return ResponseHelper::success([
            'message' => 'you can not delete this request',
        ]);
    }
}

public function accepteRequest(Request $request)
{
    $request->update(
        [
            'status'=>'accepted'
        ]
        );
        return ResponseHelper::success([
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
public function getComlaints()
{
    $result=Request::query()->where('type','complaint')->get();
    return ResponseHelper::success($result,'your request');
}

}
