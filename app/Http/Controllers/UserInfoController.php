<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UpdateUserInfoRequest;
use App\Http\Requests\UserInfoRequest;
use App\Models\User;
use App\Models\UserSalary;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\Files;
use Carbon\Carbon;

class UserInfoController extends Controller
{
    public function store(UserInfoRequest $request)
    {
        $validate = $request->validated();
        $path = Files::saveImage($request);
        $validate['image'] = $path;
        return DB::transaction(function () use ($validate) {
            $userInfo = UserInfo::query()->create($validate);
            return ResponseHelper::success($userInfo, null);
        });
        return ResponseHelper::error('error', null);
    }
    public function update(UpdateUserInfoRequest $request, $id)
    {
        $validate = $request->validated();
        if ($request->image) {
            $path = Files::saveImage($request);
            $validate['image'] = $path;
        }
        return DB::transaction(function () use ($validate, $id) {
            UserInfo::query()
                ->where('user_id', $id)
                ->update($validate);
            return ResponseHelper::success('info has been updated', null);
        });
        return ResponseHelper::error('error', null);
    }
    public function show($id)
    {
        $userId = Auth::id() ?? $id;
        $result = User::query()
            ->with('userInfo', 'address', 'notes', 'languages', 'certificates', 'study_situations')
            ->where('id', $userId)
            ->first()->toArray();
        return ResponseHelper::success([$result], null);
    }

    public function destroy($id)
    {
        $career = UserInfo::query()->find($id);
        return DB::transaction(function () use ($career) {
            $career->delete();
            return ResponseHelper::success('info has been deleted', null);
        });
        return ResponseHelper::error('not deleted', null);
    }


    // public function updateSalary(Request $request,  $id)
    // {
    //     $salary = $request->salary;
    //     $result = UserInfo::query()
    //         ->where('id', $id)
    //         ->update([
    //             'salary' => $salary
    //         ]);
    //     if ($result) {
    //         UserSalary::query()->create([
    //             'user_id' => $id,
    //             'date' => Carbon::now()->format('Y-m'),
    //             'salary' => $salary
    //         ]);
    //         return ResponseHelper::updated('salary updated', null);
    //     }
    //     return ResponseHelper::error('not updated', null);
    // }

    public function updateSalary(Request $request, $id)
    {
        $salary = $request->salary;
        $result = UserInfo::query()->where('id', $id)->first();
        return DB::transaction(function () use ($result, $salary, $id) {
            $result->update([
                'salary' => $salary
            ]);
            if ($result) {
                UserSalary::query()->create([
                    'user_id' => $id,
                    'date' => Carbon::now()->format('Y-m'),
                    'salary' => $salary
                ]);
                return ResponseHelper::updated('Salary updated', null);
            }
        });
        return ResponseHelper::error('Not updated', null);
    }
}
