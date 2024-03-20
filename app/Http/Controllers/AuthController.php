<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Services\UserRegisterService;
use TADPHP\TADFactory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Language;
use App\Models\Skills;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\Files;
use App\Models\AdditionalFile;
use App\Models\Career;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Deposit;
use App\Models\StudySituation;
use App\Models\UserSalary;
use Carbon\Carbon;
use App\Services\EditUserService;
use Exception;
use Illuminate\Support\Facades\DB;

require 'tad/vendor/autoload.php';


class AuthController extends Controller
{
    public $userRegisterService;

    public function __construct(UserRegisterService $userRegisterService)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        $this->userRegisterService = $userRegisterService;
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
                $path = Files::saveImageProfile($request->image);
            }
            $userInfo = $this->userRegisterService->createUserInfo($request, $user, $path);
            $userSalary = $this->userRegisterService->createUserSalary($user, $userInfo);
            $educations = $request->educations;
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

}

