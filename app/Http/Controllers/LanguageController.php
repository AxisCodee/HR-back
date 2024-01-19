<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\LanguageRequest;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    public function store(LanguageRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $lang = Language::query()->create($validate);
            return ResponseHelper::success($lang, null);
        });
        return ResponseHelper::error('error', null);
    }
    // public function update(StudySitRequest $request, $id)
    // {
    //     $validate = $request->validated();
    //     return DB::transaction(function () use ($validate, $id) {
    //         Language::query()
    //             ->findOrFail($id) //????
    //             ->update($validate);
    //         return ResponseHelper::success('Study has been updated', null);
    //     });
    //     return ResponseHelper::error('error', null);
    // }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $lang = Language::query()->findOrFail($id);
            $lang->delete();
            return ResponseHelper::success('Language has been deleted', null);
        });
        return ResponseHelper::error('not deleted', null);
    }
}
