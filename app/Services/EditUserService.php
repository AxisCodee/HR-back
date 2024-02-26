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
                'first_name' => $request->first_name?:$user->first_name,
                'middle_name' => $request->middle_name?:$user->middle_name,
                'last_name' => $request->last_name?:$user->last_name,
                'email' => $request->email?:$user->email,
                'role' =>  $request->role?:$user->role,
                'specialization' => $request->specialization?:$user->specialization,
                'department_id' => $request->department_id,
                'password' => Hash::make($request->password)?:$user->password,
                'pin' => $request->pin?:$user->pin,
                'address' => $request->address?:$user->address,
                'branch_id' => $request->branch_id?:$user->branch_id,
            ]);

            $path = null;
            if ($request->image) {
                $path = Files::saveImageProfile($request->image);
            }

            $userInfo = UserInfo::where('user_id',$user->id)->first();

           $userInfo->update([
                'salary' => $request->salary?:$userInfo->salary,
                'birth_date' => $request->birth_date?:$userInfo->birth_date,
                'gender' => $request->gender?:$userInfo->gender,
                'nationalID' => $request->nationalID?:$userInfo->nationalID,
                'social_situation' => $request->social_situation?:$userInfo->social_situation,
                'level' => $request->level?:$userInfo->level,
                'military_situation' => $request->military_situation?:$userInfo->military_situation,
                'health_status' => $request->health_status?:$userInfo->health_status,
                'image' => $path?:$userInfo->image
            ]);
            $user->assignRole($request->role);
            $sal= UserSalary::where('user_id',$user->id);
            $sal->update([
                'date' => Carbon::now()->format('Y-m'),
                'salary' => $request->salary?:$userInfo->salary
            ]);

            $educations = $request->educations;
            $certificates = $request->certificates;
            $languages = $request->languages;
            $skills = $request->skills;
            $experiences = $request->experiences;
            $contacts = $request->contacts;
            $secretaraits = $request->secretaraits;
            $emergency_contact = $request->emergency_contact;
            if($educations)
{
            foreach ($educations as $education) {
                $studies = StudySituation::where('user_id',$user->id);
                $studies->update([
                    'degree' => $education['degree'],
                    'study' => $education['study'],
                ]);
            }
        }
        if($certificates)
        {

            foreach ($certificates as $index => $certificate) {
                $cerities = Certificate::where('user_id',$user->id);
                $cerities->update([
                    'user_id' => $user->id,
                    'content' => $certificate,
                ]);
            }
        }
        if($languages)
{
            foreach ($languages as $language) {
                $oldLang = Language::where('user_id',$user->id);
                $oldLang->update(
                    [
                        'languages' => $language['languages'],
                    'rate' => $language['rate']
                ]);
            }
        }
        if($skills)
        {

            foreach ($skills as $skill) {
                $oldSkill = Skills::where('user_id',$user->id);
                $oldSkill->update([
                    'skills' => $skill['skills'],
                    'rate' => $skill['rate'],

                ]);
            }
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
            if($experiences)
            {
            foreach ($experiences as $experience) {
                $new_exp = Career::where('user_id',$user->id);
                $new_exp->update([
                    'content' => $experience,
                ]);
            }
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
                            'type' => 'emergency',
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
if($secretaraits)
{
            foreach ($secretaraits as $secretarait) {
                $oldRecieved = Deposit::where('user_id',$user->id);
                $oldRecieved->update([
                    'description' => $secretarait['object'],
                    'received_date' => $secretarait['delivery_date'],
                ]);
            }
        }
$result='user updated successfully';
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

