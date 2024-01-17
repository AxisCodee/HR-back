<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Decision;
use App\Models\User;
use App\Http\Requests\DecisionRequest;
use Illuminate\Support\Facades\Auth;

class DecisionController extends Controller
{
//add new decision for a user
    public function new_decision(DecisionRequest $request)
    {
        $new = $request->validated();
        $created = Decision::create($new);
        return ResponseHelper::created($created,'decision created successfully');
    }
//delete an exisiting decision
    public function remove_decision($id)
    {
        $removed = Decision::findOrFail($id)
                            ->delete();
        return ResponseHelper::deleted(' decision deleted successfully');
    }
//edit an exisiting decision
    public function edit_decision(DecisionRequest $request,$id)
    {
        $validate = $request->validated();
        $edited = Decision::findOrFail($id)->with('user_decision');
        $edited->update($validate);
        return ResponseHelper::updated($edited,'decision updated successfully');
    }
//get all decisions for all users
    public function all_decisions()
    {
        $all = Decision::query()
                        ->with('user_decision')
                        ->get();
        return ResponseHelper::success($all, null, 'all decisions returned successfully', 200);
    }
//get decisions for the current user
    public function my_decisions()
    {
        $mine = Decision::query()
                        ->where('user_id',Auth::id())
                        ->get();
        return ResponseHelper::success($mine, null, 'user decisions returned successfully', 200);
    }
}
