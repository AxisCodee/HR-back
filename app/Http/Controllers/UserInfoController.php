<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UserInfoRequest;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserInfoController extends Controller
{
    public function store(UserInfoRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $userInfo = UserInfo::query()->updateOrCreate($validate);
            return ResponseHelper::success($userInfo, null);
        });
        return ResponseHelper::error(['error'], null);
    }

    public function show()
    {
        $result = User::query()
            ->with('userInfo')
            ->where('id', Auth::id())
            ->get()->toArray();
        return ResponseHelper::success($result, null, 200);
    }
}
