<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\StudySitRequest\StoreStudySitRequest;
use App\Http\Requests\StudySitRequest\UpdateStudySitRequest;
use App\Models\StudySituation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudySituationController extends Controller
{
    public function store(StoreStudySitRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $studySit = StudySituation::query()->create($validate);
            return ResponseHelper::success($studySit, null);
        });
        return ResponseHelper::error('error', null);
    }
    public function update(UpdateStudySitRequest $request, $id)
    {
        $validate = $request->validated();
        $studySit = StudySituation::query()->findOrFail($id);
        return DB::transaction(function () use ($validate, $studySit) {
            $studySit->update($validate);
            return ResponseHelper::success('Study has been updated', null);
        });
        return ResponseHelper::error('error', null);
    }

    public function destroy($id)
    {
        $studySit = StudySituation::query()->findOrFail($id);
        return DB::transaction(function () use ($studySit) {
            $studySit->delete();
            return ResponseHelper::success('Study has been deleted', null);
        });
        return ResponseHelper::error('not deleted', null);
    }
}
