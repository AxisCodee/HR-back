<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\StudySitRequest;
use App\Http\Requests\UpdateStudySitRequest;
use App\Models\StudySituation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudySituationController extends Controller
{
    public function store(StudySitRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $stuSit = StudySituation::query()->create($validate);
            return ResponseHelper::success($stuSit, null);
        });
        return ResponseHelper::error('error', null);
    }
    public function update(UpdateStudySitRequest $request, $id)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate, $id) {
            StudySituation::query()
                ->findOrFail($id) //????
                ->update($validate);
            return ResponseHelper::success('Study has been updated', null);
        });
        return ResponseHelper::error('error', null);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $stuSit = StudySituation::query()->findOrFail($id);
            $stuSit->delete();
            return ResponseHelper::success('Study has been deleted', null);
        });
        return ResponseHelper::error('not deleted', null);
    }
}
