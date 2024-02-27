<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\UserRequest\StoreUserRequest;
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
        try {
            $validate = $request->validated();
            return DB::transaction(function () use ($request) {
                $branch_id = $request->branch_id;
                if ($request->has('department_id')) {
                    $department = Department::find($request->department_id);
                    $department_id = $department ? $department->id : null;
                } else {
                    $department_id = null;
                }

                $user = User::create([
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'role' => 'employee',
                    'specialization' => $request->specialization,
                    'department_id' => $department_id,
                    'password' => Hash::make($request->password),
                    'pin' => null,
                    'address' => $request->address,
                    'branch_id' => $branch_id,
                ]);
                $user->update(['pin' => $user->id]);

                //    $tad_factory = new TADFactory(['ip' => '192.168.2.202']);
                // $tad = $tad_factory->get_instance();
                // $r = $tad->set_user_info([
                //    'pin' => $user->id,//this is the pin2 in the returned response
                //    'name'=> $request->first_name,
                //    'privilege'=> 0,//if you want to add a superadmin user make the privilege as '14'.
                //    'password' => $request->password]);
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
                $user->assignRole('employee');

                $sal = UserSalary::query()->create([
                    'user_id' => $user->id,
                    'date' => Carbon::now()->format('Y-m'),
                    'salary' => $userInfo->salary
                ]);

                $educations = $request->educations;
                $certificates = $request->certificates;
                $languages = $request->languages;
                $skills = $request->skills;
                $experiences = $request->experiences;
                $contacts = $request->contacts;
                $secretaraits = $request->secretaraits;
                $emergency_contact = $request->emergency_contact;


                foreach ($educations as $education) {
                    if (isset($education['degree']) && isset($education['study'])) {

                    $studies = StudySituation::query()->create([
                        'degree' => $education['degree'],
                        'study' => $education['study'],
                        'user_id' => $user->id,
                    ]);}
                }



                foreach ($certificates as $index => $certificate) {
                    if (isset($certificate['content']) ) {

                    $cerities = Certificate::query()->create([
                        'user_id' => $user->id,
                        'certificates[0][content]' => $certificate,
                    ]);}
                }


                foreach ($languages as $language) {
                    if (isset($language['languages']) && isset($language['rate'])) {

                    $language = Language::query()->create([
                        'languages' => $language['languages'],
                        'rate' => $language['rate'],
                        'user_id' => $user->id,
                    ]);}
                }

                foreach ($skills as $skill) {
                    if (isset($skill['skills']) && isset($skill['rate'])) {

                    $skill = Skills::query()->create([
                        'skills' => $skill['skills'],
                        'rate' => $skill['rate'],
                        'user_id' => $user->id,
                    ]);}
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
                    if ( isset($experience['content'])) {

                    $new_exp = Career::query()->create([
                        'user_id' => $user->id,
                        'experiences[0][content]' => $experience
                    ]);}
                }

                if (isset($contacts['emails'][0])) {
                    foreach ($contacts['emails'] as $contact) {
                        $multi = Contact::create([
                            'user_id' => $user->id,
                            'type' => 'normal',
                            'email' => $contact['email'],
                        ]);
                    }
                }

                if (isset($contacts['phonenumbers'])) {
                    foreach ($contacts['phonenumbers'] as $contact) {
                        $multi = Contact::create([
                            'user_id' => $user->id,
                            'type' => 'normal',
                            'phone_num' => $contact['phone'],
                        ]);
                    }
                }

                if ($request->emergency_contact) {

                    foreach ($emergency_contact as $emergency) {
                        if (isset($emergency['phonenumber']) || isset($emergency['email'])) {
                            $contact = Contact::query()->create([
                                'user_id' => $user->id,
                                'type' => "emergency",
                                'name' => $emergency['name'],
                                'address' => $emergency['address'],
                                'phone_num' => $emergency['phonenumber'] ?? null,
                                'email' => $emergency['email'] ?? null,
                            ]);
                        } else {
                            throw new Exception("Emergency contact must have either a phone number or an email.");
                        }
                    }
                }

                foreach ($secretaraits as $secretarait) {
                    if (isset($secretarait['delivery_date']) && isset($secretarait['object'])) {
                        $recieved = Deposit::query()->create([
                            'user_id' => $user->id,
                            'description' => $secretarait['object'],
                            'recieved_date' => $secretarait['delivery_date'],
                        ]);
                    }
                }


                return ResponseHelper::success($user);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle the validation exception and return an error response with the validation errors
            $errorMessage = $e->validator->errors()->first();
            return ResponseHelper::error($errorMessage, null);
        } catch (\Exception $e) {
            // Handle other exceptions and return an error response
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
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

