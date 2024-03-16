<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UserInfoRequest\UpdateUserInfoRequest;
use App\Http\Requests\UserInfoRequest\StoreUserInfoRequest;
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
    public function store(StoreUserInfoRequest $request)
    {
        $validate = $request->validated();
        $path = Files::saveImage($request);
        $validate['image'] = $path;
        return DB::transaction(function () use ($validate) {
            $userInfo = UserInfo::query()->create($validate);
            return ResponseHelper::success($userInfo, null);
        });
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
    }

    public function show($id)
    {
        $userId = Auth::id() ?? $id;
        $result = User::query()
            ->with('userInfo', 'notes', 'languages', 'certificates', 'study_situations', 'absences')
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
        try {
            $salary = $request->salary;
            $result = UserInfo::findOrFail($id)->first();
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
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
