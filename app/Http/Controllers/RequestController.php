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
             'user_id'=>Auth::id(),
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
    public function update(UpdateRequestRequest $request, $id)
{
    $request = Request::query()
        ->where('id', $id)
        ->where('status', 'waiting')
        ->update([
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description
        ]);

    if ($request) {

        return ResponseHelper::updated('Request updated successfully');
    } else {
        return ResponseHelper::success('You cannot update this request');
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

        return ResponseHelper::deleted(
            'request deleted successfully'

        );

    }
    else
    {
        return ResponseHelper::error(
         'you can not delete this request'
        ,null,'error', 403);
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
    $result=Request::query()->with('users')
    ->where('type','complaint')
    ->get()->toArray();
    return ResponseHelper::success($result,'your request');
}

}
