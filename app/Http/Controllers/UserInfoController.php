<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UserInfoRequest\UpdateUserInfoRequest;
use App\Http\Requests\UserInfoRequest\StoreUserInfoRequest;
use App\Models\User;
use App\Models\UserSalary;
use App\Models\UserInfo;
use App\Services\AbsenceService;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserInfoController extends Controller
{
    protected $absenceService;
    protected $fileService;

    public function __construct(AbsenceService $absenceService, FileService $fileService)
    {
        $this->absenceService = $absenceService;
        $this->fileService = $fileService;
    }

    public function store(StoreUserInfoRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $path = $request->has('image') ? $this->fileService->upload($request->image, 'image') : null;
            $userInfo = UserInfo::query()->create(array_merge($request->all(), ['image' => $path]));
            return ResponseHelper::success($userInfo);
        });
    }

    public function update(UpdateUserInfoRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $userInfo = UserInfo::query()
                ->where('user_id', $id)
                ->first();
            if (!$userInfo) {
                return ResponseHelper::error('User Info not found for this user.');
            }
            $path = $userInfo->image
                ? $this->fileService->update($userInfo->image, $request->image, 'image')
                : ($request->has('image') ? $this->fileService->upload($request->image, 'image') : null);
            UserInfo::query()
                ->where('user_id', $id)
                ->update(array_merge($request->all(), ['image' => $path]));
            return ResponseHelper::success('updated successfully');
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

    public function updateSalary(Request $request, $id)
    {
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
            return ResponseHelper::error('not updated');
        });

    }

    public function setDemandCompensationHours($id, Request $request)
    {
        UserInfo::query()
            ->where('user_id', $id)
            ->increment('compensation_hours', $request->compensation_hours);
        return ResponseHelper::success('updated');
    }

    public function getCompensationHours(User $user)
    {
        $totalAbsenceHours = $this->absenceService->totalAbsenceHours($user->id, null);
        $compensationHours = $user->userInfo->compensation_hours;
        return ResponseHelper::success(
            ['compensation_hours' => $totalAbsenceHours - $compensationHours],
            'compensation hours returned successfully');
    }

}
