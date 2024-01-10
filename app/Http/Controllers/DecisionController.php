<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Decision;
use App\Models\User;
use App\Http\Requests\DecisionRequest;

class DecisionController extends Controller
{
    public function new_decision(DecisionRequest $request)
    {
        $new = $request->validated();
        $created = Decision::create($new);
        return ResponseHelper::success($created, null, ' decision created successfully', 200);
    }

    public function remove_decision($id)
    {
        $removed = Decision::findOrFail($id)->delete();
        return ResponseHelper::success($removed, null, ' decision deleted successfully', 200);
    }

    public function edit_decision(DecisionRequest $request,$id)
    {
        $validate = $request->validated();
        $edited = Decision::findOrFail($id);
        $edited->update($validate);
        return ResponseHelper::success($edited, null, ' decision edited successfully', 200);
    }

    public function all_decisions()
    {
        $all = Decision::query()->get();
        return ResponseHelper::success($all, null, 'all decisions returned successfully', 200);
    }
}
