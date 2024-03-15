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
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'pin' => $request->pin?:$user->pin,
                'address' => $request->address?:$user->address,
                'branch_id' => $request->branch_id?:$user->branch_id,
                'permission'=>$request->permission

            ]);

            $path = null;
            if ($request->image) {
                $path = Files::saveImageProfile($request->image);
            }

            $userInfo = UserInfo::where('user_id',$user->id)->first();

            if($request->salary && $userInfo->salary != $request->salary)
            {
                $lastSalaryUpdate = $user->salary()->latest('date')->first();

                $lastSalaryDate = $lastSalaryUpdate ? $lastSalaryUpdate->date : null;
                $monthsToCreate = $lastSalaryDate ? Carbon::parse($lastSalaryDate)
                ->diffInMonths(Carbon::now()->format('Y-m')) : 0;

                for ($i = 0; $i < $monthsToCreate-1; $i++) {
                    $date = Carbon::now()->subMonths($i + 1)->startOfMonth()->format('Y-m');
                    $user->salary()->create([
                        'date' => $date,
                        'salary' => $lastSalaryUpdate ? $lastSalaryUpdate->salary : 0,
                    ]);
                }
                $user->salary()->create([
                    'user_id'=>$user->id,
                    'date' => Carbon::now()->startOfMonth()->format('Y-m'),
                    'salary' => $request->salary,
                ]);
            }

           $userInfo->update([
                'salary' => $request->salary?:$userInfo->salary,
                'birth_date' => $request->birth_date?:$userInfo->birth_date,
                'gender' => $request->gender?:$userInfo->gender,
                'nationalID' => $request->nationalID?:$userInfo->nationalID,
                'social_situation' => $request->social_situation?:$userInfo->social_situation,
                'level' => $request->level?:$userInfo->level,
                'military_situation' => $request->military_situation?:$userInfo->military_situation,
                'health_status' => $request->health_status?:$userInfo->health_status,
                'image' => $path?:$userInfo->image?:$userInfo->image,
            ]);
            if($request->role)
            {
            $user->assignRole($request->role);
            }
            $educations = $request->educations;
            $certificates = $request->certificates;
            $languages = $request->languages;
            $skills = $request->skills;
            $experiences = $request->experiences;
            $contacts = $request->contacts;
            $secretaraits = $request->secretaraits;
            $emergency_contact = $request->emergency_contact;

            $studies = StudySituation::where('user_id',$user->id)->delete();
            $cerities = Certificate::where('user_id',$user->id)->delete();
            $language = Language::where('user_id',$user->id)->delete();
            $skill = Skills::where('user_id',$user->id)->delete();
            $add_file = AdditionalFile::where('user_id',$user->id)->delete();
            $new_exp = Career::where('user_id',$user->id)->delete();
            $multi = Contact::where('user_id',$user->id)->delete();
            $received = Deposit::where('user_id',$user->id)->delete();

            if($educations)
            {
            foreach ($educations as $education) {
                if (isset($education['degree']) && isset($education['study'])) {

                $studies = StudySituation::query()->create([
                    'degree' => $education['degree'],
                    'study' => $education['study'],
                    'user_id' => $user->id,
                ]);}
            }

        }
        if($certificates)
        {

            foreach ($certificates as $index => $certificate) {
                if (isset($certificate['content'])) {
                    $cerities = Certificate::query()->create([
                        'user_id' => $user->id,
                        'content' => $certificate['content'],
                    ]);
                }
            }
        }
        if($languages)
        {

            foreach ($languages as $language) {
                if (isset($language['languages']) && isset($language['rate'])) {

                $language = Language::query()->create([
                    'languages' => $language['languages'],
                    'rate' => $language['rate'],
                    'user_id' => $user->id,
                ]);}
            }
        }
        if($skills)
        {
            foreach ($skills as $skill) {
                if (isset($skill['skills']) && isset($skill['rate'])) {

                $skill = Skills::query()->create([
                    'skills' => $skill['skills'],
                    'rate' => $skill['rate'],
                    'user_id' => $user->id,
                ]);}
            }
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
            if($experiences)
            {
            foreach ($experiences as $experience) {
                if (isset($experience['content'])) {
                    $new_exp = Career::query()->create([
                        'user_id' => $user->id,
                        'content' => $experience['content'],
                    ]);
                }
            }
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
                        'phone_num' => $contact['phone_num'],
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
                            'phone_num' => $emergency['phone_num'] ?? null,
                            'email' => $emergency['email'] ?? null,
                        ]);
                    } else {
                        throw new Exception("Emergency contact must have either a phone number or an email.");
                    }
                }
            }
            if ($request->secretaraits) {

            foreach ($secretaraits as $secretarait) {
                if (isset($secretarait['delivery_date']) && isset($secretarait['object'])) {
                    $received = Deposit::query()->create([
                        'user_id' => $user->id,
                        'description' => $secretarait['object'],
                        'received_date' => $secretarait['delivery_date'],
                    ]);
                }
            }
            }
            $result='updated successfully';

            return $result;

    //         return ResponseHelper::success('updated successfully');
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
}
