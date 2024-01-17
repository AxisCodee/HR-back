<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Http\Requests\CareerRequest;
use Illuminate\Support\Facades\DB;


class CareerController extends Controller
{
    public function store(CareerRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $career = Career::query()->updateOrCreate($validate);
            return ResponseHelper::success($career, null);
        });
        return ResponseHelper::error(['error'], null);
    }
    public function destroy(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $career = Career::query()->find($request->career_id);
            $career->delete();
            return ResponseHelper::success('Career has been deleted', null);
        });
        return ResponseHelper::error(['not deleted'], null);
    }
}
