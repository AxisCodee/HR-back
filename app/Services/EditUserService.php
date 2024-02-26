<?php

namespace App\Services;


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
use Exception;
use Illuminate\Support\Facades\DB;


class EditUserService
{

public  function updateUser($user,$request)
{

    try {
        //$validate = $request->validated();
        return DB::transaction(function () use ($request ,$user) {



           $result= $user->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' =>  $request->role,
                'specialization' => $request->specialization,
                'department_id' => $request->department_id,
                'password' => Hash::make($request->password),
                'pin' => $request->pin,
                'address' => $request->address,
                'branch_id' => $request->branch_id,
            ]);

            $path = null;
            if ($request->image) {
                $path = Files::saveImageProfile($request->image);
            }
            $userInfo=UserInfo::where('user_id',$user->id);
           $userInfo->update([
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
            $sal= UserSalary::where('user_id',$user->id);
            $sal->update([
                'date' => Carbon::now()->format('Y-m'),
                'salary' => $request->salary
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
                $studies = StudySituation::where('user_id',$user->id);
                $studies->update([
                    'degree' => $education['degree'],
                    'study' => $education['study'],
                ]);
            }

            foreach ($certificates as $index => $certificate) {
                $cerities = Certificate::where('user_id',$user->id);
                $cerities->update([
                    'user_id' => $user->id,
                    'content' => $certificate,
                ]);
            }

            foreach ($languages as $language) {
                $oldLang = Language::where('user_id',$user->id);
                $oldLang->update(
                    ['name' => $language['languages'],
                    'rate' => $language['rate']
                ]);
            }

            foreach ($skills as $skill) {
                $oldSkill = Skills::where('user_id',$user->id);
                $oldSkill->update([
                    'name' => $skill['skills'],
                    'rate' => $skill['rate'],

                ]);
            }

            if ($request->additional_files) {
                foreach ($request->additional_files as $file) {
                    (function ($file) use ($user) {
                        $filepath = null;
                        $filepath = Files::saveFileF($file['file']);
                        $oldAdd_file = AdditionalFile::where('user_id',$user->id);
                        $oldAdd_file->update([
                            'description' => $file['description'],
                            'path' => $filepath,
                        ]);
                    })($file);
                }
            }
            foreach ($experiences as $experience) {
                $new_exp = Career::where('user_id',$user->id);
                $new_exp->update([
                    'content' => $experience,
                ]);
            }

            if (isset($contacts['emails'][0])) {
                foreach ($contacts['emails'] as $contact) {
                    $multi = Contact::where('user_id',$user->id);
                    $multi->update([
                        'type' => 'normal',
                        'contact' => $contact['email'],
                    ]);
                }
            }

            if (isset($contacts['phonenumbers'])) {
                foreach ($contacts['phonenumbers'] as $contact) {
                    $multi = Contact::where('user_id',$user->id);
                    $multi->update([
                        'type' => 'normal',
                        'contact' => $contact['phone'],
                    ]);
                }
            }

            if ($request->emergency_contact) {

                foreach ($emergency_contact as $emergency) {
                    if (isset($emergency['phonenumber']) || isset($emergency['email'])) {

                        $contact = Contact::where('user_id',$user->id);
                        $contact->update([
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
                $oldRecieved = Deposit::where('user_id',$user->id);
                $oldRecieved->update([
                    'description' => $secretarait['object'],
                    'received_date' => $secretarait['delivery_date'],
                ]);
            }
$result='user created successfully';
       return $result;
        });
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle the validation exception and return an error response with the validation errors
        $errorMessage = $e->validator->errors()->first();
        return $errorMessage;
    } catch (\Exception $e) {
        // Handle other exceptions and return an error response
        $exception=[
            'message'=>$e->getMessage(),
            'code'=> $e->getCode()
        ];
        return $exception;
    }
}

}

