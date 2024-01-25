<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use TADPHP\TADFactory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdditionalFileRequest;
use App\Models\Certificate;
use App\Models\Language;
use App\Models\Skils;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\Files;
use App\Models\AdditionalFile;
use App\Models\Career;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

require 'tad/vendor/autoload.php';

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
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
        return ResponseHelper::success([
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {

        $tad_factory = new TADFactory(['ip' => '192.168.2.202']);
        $tad = $tad_factory->get_instance();
        // $r = $tad->set_user_info([
        //     'pin' => $request->id,//this is the pin2 in the returned response
        //     'name'=> $request->first_name,
        //     'privilege'=> 0,//if you want to add a superadmin user make the privilege as '14'.
        //     'password' => $request->password]);
        $request->validate([
            'first_name' => 'required|string|max:50',
            'middle_name' => 'string|max:50',
            'last_name' => 'string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
            'specialization'=>$request->specialization,
            'department_id' => $request->department_id,
            'password' => Hash::make($request->password),
            'pin' => $request->pin, //this is the pin2 in the returned response
            'address' => $request->address
        ]);
        $path = Files::saveImage($request);
        $userInfo = UserInfo::query()->create([
            'user_id' => $user->id,
            'salary' => $request->salary,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'nationalID' => $request->nationalID,
            'social_situation' => $request->social_situation,
            'military_situation' => $request->military_situation,
            'image' => $path


        ]);
        $user->assignRole($request->role);


        $certificates = $request->certificates;
        $languages = $request->languages;
        $languageRates = $request->languageRates;
        $skills = $request->skills;
        $skillsRates = $request->skillsRates;
        $experiences = $request->experiences;
        //$files = $request->files;

        foreach ($certificates as $index=>$certificatestudy) {
            $certificate_degree = $request->certificate_degree[$index];
            $certificate = Certificate::create([
                'degree' => $certificatestudy,
                'study'  => $certificate_degree,
                'user_id' => $user->id,
            ]);
        }

        foreach ($languages as $index => $languageName) {
            $languageRate = $request->languageRates[$index];
            $language = Language::create([
                'name' => $languageName,
                'rate' => $languageRate,
                'user_id' => $user->id,
            ]);
        }

        foreach ($skills as $index => $skillsName) {
            $skillsRate = $request->skillsRates[$index];
            $skill = Skils::create([
                'name' => $skillsName,
                'rate' => $skillsRate,
                'user_id' => $user->id,
            ]);
        }

        if ($request->hasfile('path')) {
            foreach ($request->path as $index => $file)
            {
                (function ($file) use ($user, $index,$request) {
                        $path=Files::saveFileF($file);
                        $filedescription = $request->filedescription[$index];
                        $add_file = AdditionalFile::create([
                            'user_id' => $user->id,
                            'description' => $filedescription,
                            'path' => $path
                        ]);
                })($file);
            }
        }

        foreach ($experiences as $experience) {
            $new_exp = Career::create([
                'user_id' => $user->id,
                'content' => $experience
            ]);
        }


        $multi = Contact::query()->create([
            'user_id' => $user->id,
            'type' => $request->contactType,
            'name' => ($request->contactType === 'emergency') ? $request->contactName : null,
            'address' => ($request->contactType === 'emergency') ? $request->contactAddress : null,
            'contact' => $request->contact
        ]);




        return ResponseHelper::success(
            [$user]
        );
    }

    public function logout()
    {
        Auth::logout();
        return  ResponseHelper::success([
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
