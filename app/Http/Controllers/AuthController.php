<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Services\FileService;
use App\Services\UserRegisterService;
use Illuminate\Http\Request;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

require 'tad/vendor/autoload.php';


class AuthController extends Controller
{
    public $userRegisterService;
    public $fileService;

    public function __construct(UserRegisterService $userRegisterService, FileService $fileService)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        $this->userRegisterService = $userRegisterService;
        $this->fileService = $fileService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);
        if (!$token) {
            return ResponseHelper::error('email or password are not correct', null, 'error', 401);
        }
        $user = Auth::user();
        $userInfo = UserInfo::where('user_id', $user->id)->first();
        return ResponseHelper::success([
            'user' => $user,
            'user_info' => $userInfo,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(StoreUserRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $branch_id = $request->branch_id;
            if ($request->has('department_id')) {
                $department = Department::find($request->department_id);
                $department_id = $department ? $department->id : null;
            } else {
                $department_id = null;
            }
            $user = $this->userRegisterService->createUser($request, $department_id, $branch_id);
            $path = null;
            if ($request->image) {
                $path = $this->fileService->upload($request->image, 'image');
            }
            $userInfo = $this->userRegisterService->createUserInfo($request, $user, $path);
            $this->userRegisterService->createUserSalary($user, $userInfo);
            $educations = $request->educations;
            $request->contract;
            $certificates = $request->certificates;
            $languages = $request->languages;
            $skills = $request->skills;
            $experiences = $request->experiences;
            $contacts = $request->contacts;
            $secretaraits = $request->secretaraits;
            $emergency_contact = $request->emergency_contact;
            $this->userRegisterService->createUserStudySituations($user, $educations);
            $this->userRegisterService->createUserCertificates($user, $certificates);
            $this->userRegisterService->createUserLanguages($user, $languages);
            $this->userRegisterService->createUserSkills($user, $skills);
            if ($request->additional_files) {
                $this->userRegisterService->createUserFiles($user, $request);
            }
            $this->userRegisterService->createUserExperiences($user, $experiences);
            if (isset($contacts['emails'][0])) {
                $this->userRegisterService->createUserContacts($user, $contacts);
            }
            if (isset($contacts['phonenumbers'])) {
                $this->userRegisterService->createUserPhoneNumbers($user->id, $contacts);
            }
            if ($request->emergency_contact) {
                $this->userRegisterService->createUserEmergencyContact($user->id, $emergency_contact);
            }
            if ($request->secretaraits) {
                $this->userRegisterService->createUserDeposits($user->id, $secretaraits);
            }
            if ($request->contract) {
                $this->userRegisterService->CreateUsercontract($user->id, $request->contract);
            }
            return ResponseHelper::success($user);
        });

    }


    public function logout()
    {
        Auth::logout();
        return ResponseHelper::success([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function me()
    {
        $user = Auth::user();
        if ($user) {
            return ResponseHelper::success([$user]);
        }
        return ResponseHelper::error('You are not authorized.', 401);
    }

}

