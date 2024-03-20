<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\LanguageRequest\StoreLanguageRequest;
use App\Http\Requests\LanguageRequest\UpdateLanguageRequest;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    public function store(StoreLanguageRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $lang = Language::query()->create($validate);
            return ResponseHelper::success($lang, null);
        });
    }

    public function update(UpdateLanguageRequest $request, $id)
    {
        $validate = $request->validated();
        $lang = Language::query()->findOrFail($id);
        return DB::transaction(function () use ($validate, $lang) {
            $lang->update($validate);
            return ResponseHelper::success('Language has been updated', null);
        });
    }

    public function destroy($id)
    {
        $lang = Language::query()->findOrFail($id);
        return DB::transaction(function () use ($lang) {
            $lang->delete();
            return ResponseHelper::success('Language has been deleted', null);
        });
    }
}
