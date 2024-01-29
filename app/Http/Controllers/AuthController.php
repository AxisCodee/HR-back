<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use TADPHP\TADFactory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddUserRequest;
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
use App\Models\Deposit;
use App\Models\StudySituation;
use Illuminate\Support\Facades\DB;


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

    public function register(AddUserRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($request) {
            // $tad_factory = new TADFactory(['ip' => '192.168.2.202']);
            // $tad = $tad_factory->get_instance();
            //$r = $tad->set_user_info([
            //    'pin' => $request->id,//this is the pin2 in the returned response
            //    'name'=> $request->first_name,
            //    'privilege'=> 0,//if you want to add a superadmin user make the privilege as '14'.
            //    'password' => $request->password]);

            $user = User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => $request->role,
                'specialization' => $request->specialization,
                'department_id' => $request->department_id,
                'password' => Hash::make($request->password),
                'pin' => $request->pin, //this is the pin2 in the returned response
                'address' => $request->address
            ]);
            $path = null;
            if ($request->image) {
                $path = Files::saveImageProfile($request->image);
            }
            $userInfo = UserInfo::query()->create([
                'user_id' => $user->id,
                'salary' => $request->salary,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'nationalID' => $request->nationalID,
                'social_situation' => $request->social_situation,
                'level' => $request->level,
                'military_situation' => $request->military_situation,
                'health_status' => $request->health_status,
                'image' => $path
            ]);
            $user->assignRole($request->role);


            $educations = $request->educations;
            $certificates = $request->certificates;
            $languages = $request->languages;
            $skills = $request->skills;
            $experiences = $request->experiences;
            $contacts = $request->contacts;
            $secretaraits = $request->secretaraits;
            $emergency_contact = $request->emergency_contact;

            foreach ($educations as $education) {
                $studies = StudySituation::query()->create([
                    'degree' => $education['degree'],
                    'study'  => $education['study'],
                    'user_id' => $user->id,
                ]);
            }

            foreach ($certificates as $index => $certificate) {
                $cerities = Certificate::query()->create([
                    'user_id' => $user->id,
                    'content' => $certificate,
                ]);
            }

            foreach ($languages as  $language) {
                $language = Language::query()->create([
                    'name' => $language['languages'],
                    'rate' => $language['rate'],
                    'user_id' => $user->id,
                ]);
            }

            foreach ($skills as $skill) {
                $skill = Skils::query()->create([
                    'name' => $skill['skills'],
                    'rate' => $skill['rate'],
                    'user_id' => $user->id,
                ]);
            }



            if ($request->additional_files) {
                foreach ($request->additional_files as $file) {
                    (function ($file) use ($user) {
                        $filepath = null;
                        $filepath = Files::saveFileF($file['file']);
                        $add_file = AdditionalFile::query()->create([
                            'user_id' => $user->id,
                            'description' => $file['description'],
                            'path' => $filepath,
                        ]);
                    })($file);
                }
            }

            foreach ($experiences as $experience) {
                $new_exp = Career::query()->create([
                    'user_id' => $user->id,
                    'content' => $experience,
                ]);
            }

            if (isset($contacts['emails'])) {

                foreach ($contacts['emails'] as $contact) {

                    $multi = Contact::create([
                        'user_id' => $user->id,
                        'type' => 'email',
                        'contact' => $contact,
                    ]);
                }
            }

            if (isset($contacts['phonenumbers'])) {
                foreach ($contacts['phonenumbers'] as $contact) {
                    $multi = Contact::create([
                        'user_id' => $user->id,
                        'type' => 'user_num',
                        'contact' => $contact,
                    ]);
                }
            }



            if ($request->emergency_contact) {
                foreach ($emergency_contact as $emergency) {
                    $contact = Contact::query()->create([
                        'user_id' => $user->id,
                        'type' => "emergency",
                        'name' => $emergency['name'],
                        'address' => $emergency['address'],
                        'contact' => $emergency['contact'],
                    ]);
                }
            }

            foreach ($secretaraits as $secretarait) {
                $recieved = Deposit::query()->create([
                    'user_id' => $user->id,
                    'description' => $secretarait['object'],
                    'recieved_date' => $secretarait['delivery_date'],
                ]);
            }


            return ResponseHelper::success($user);
        });
        return ResponseHelper::error();
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
