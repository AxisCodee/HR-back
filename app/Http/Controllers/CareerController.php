<?php

namespace App\Http\Controllers;

use App\Models\Career;
use App\Helper\ResponseHelper;
use App\Http\Requests\CareerRequest;
use App\Http\Requests\UpdateCareerRequest;
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
        return ResponseHelper::error('error', null);
    }

    public function update(UpdateCareerRequest $request, $id)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate, $id) {
            Career::query()
                ->where('id', $id)
                ->update($validate);
            return ResponseHelper::success('Career has been updated', null);
        });
        return ResponseHelper::error('error', null);
    }
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $career = Career::query()->find($id);
            $career->delete();
            return ResponseHelper::success('Career has been deleted', null);
        });
        return ResponseHelper::error('not deleted', null);
    }
}
