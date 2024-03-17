<?php

namespace App\Http\Controllers;

use App\Http\Requests\CareerRequest\StoreCareerRequest;
use App\Models\Career;
use App\Helper\ResponseHelper;
use App\Http\Requests\CareerRequest\UpdateCareerRequest;
use Illuminate\Support\Facades\DB;


class CareerController extends Controller
{
    public function store(StoreCareerRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $career = Career::query()->updateOrCreate($validate);
            return ResponseHelper::success($career, null);
        });
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
    }
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $career = Career::query()->find($id);
            $career->delete();
            return ResponseHelper::success('Career has been deleted', null);
        });
    }
}
