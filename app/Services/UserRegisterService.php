<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Career;
use App\Models\Skills;
use App\Models\Contact;
use App\Models\Deposit;
use App\Models\Contract;
use App\Models\Language;
use App\Models\UserInfo;
use App\Http\Traits\Files;
use App\Models\UserSalary;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Models\AdditionalFile;
use App\Models\StudySituation;
use Illuminate\Support\Facades\Hash;

class UserRegisterService
{
    /**
     * User Register Methods
     */
    public function createUser($request, $department_id, $branch_id)
    {
        try {
            $newPin = User::query()->latest()->value('pin') + 1;
            $user = User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => 'employee',
                'specialization' => $request->specialization,
                'department_id' => $department_id,
                'password' => Hash::make($request->password),
                'pin' => $newPin,
                'address' => $request->address,
                'branch_id' => $branch_id,
                'permission' => $request->permission
            ]);
            $user->update(['pin' => $user->id]);
            return $user;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserInfo($request, $user, $path)
    {
        try {
            $userInfo = UserInfo::query()->create([
                'user_id' => $user->id,
                'salary' => $request->salary,
                'birth_date' => $request->birth_date,
                'start_date' => $request->start_date,
                'gender' => $request->gender,
                'nationalID' => $request->nationalID,
                'social_situation' => $request->social_situation,
                'level' => $request->level,
                'military_situation' => $request->military_situation,
                'health_status' => $request->health_status,
                'image' => $path
            ]);
            $user->assignRole('employee');
            return $userInfo;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserSalary($user, $userInfo)
    {
        try {
            $userSalary = UserSalary::query()->create([
                'user_id' => $user->id,
                'date' => Carbon::now()->format('Y-m') . '-00',
                'salary' => $userInfo->salary
            ]);
            return $userSalary;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserStudySituations($user, $educations)
    {
        try {
            foreach ($educations as $education) {
                if (isset($education['degree']) && isset($education['study'])) {
                    StudySituation::query()->create([
                        'degree' => $education['degree'],
                        'study' => $education['study'],
                        'user_id' => $user->id,
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserCertificates($user, $certificates)
    {
        try {
            foreach ($certificates as $index => $certificate) {
                if (isset($certificate['content'])) {
                    Certificate::query()->create([
                        'user_id' => $user->id,
                        'content' => $certificate['content'],
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserLanguages($user, $languages)
    {
        try {
            foreach ($languages as $language) {
                if (isset($language['languages']) && isset($language['rate'])) {
                    Language::query()->create([
                        'languages' => $language['languages'],
                        'rate' => $language['rate'],
                        'user_id' => $user->id,
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserSkills($user, $skills)
    {
        try {
            foreach ($skills as $skill) {
                if (isset($skill['skills']) && isset($skill['rate'])) {
                    Skills::query()->create([
                        'skills' => $skill['skills'],
                        'rate' => $skill['rate'],
                        'user_id' => $user->id,
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserFiles($user, $request)
    {
        try {
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
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserExperiences($user, $experiences)
    {
        try {
            foreach ($experiences as $experience) {
                if (isset($experience['content'])) {
                    Career::query()->create([
                        'user_id' => $user->id,
                        'content' => $experience['content'],
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserContacts($user, $contacts)
    {
        try {
            foreach ($contacts['emails'] as $contact) {
                Contact::create([
                    'user_id' => $user->id,
                    'type' => 'normal',
                    'email' => $contact['email'],
                ]);
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserPhoneNumbers($user_id, $contacts)
    {
        try {
            foreach ($contacts['phonenumbers'] as $contact) {
                Contact::create([
                    'user_id' => $user_id,
                    'type' => 'normal',
                    'phone_num' => $contact['phone_num'],
                ]);
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserEmergencyContact($user_id, $emergency_contact)
    {
        try {
            foreach ($emergency_contact as $emergency) {
                if (isset($emergency['phonenumber']) || isset($emergency['email'])) {
                    Contact::query()->create([
                        'user_id' => $user_id,
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
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function createUserDeposits($user_id, $secretaraits)
    {
        try {
            foreach ($secretaraits as $secretary) {
                if (isset($secretary['delivery_date']) && isset($secretary['object'])) {

                    $path = Files::saveFileF($secretary['path']);
                    Deposit::query()->create([
                        'user_id' => $user_id,
                        'title' => $secretary['title'],
                        'description' => $secretary['object'],
                        'path' => $path,
                        'received_date' => $secretary['delivery_date'],
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * User Update Methods
     */

    public function updateUser($request, $user)
    {
        try {
            $user->update([
                'first_name' => $request->first_name ?: $user->first_name,
                'middle_name' => $request->middle_name ?: $user->middle_name,
                'last_name' => $request->last_name ?: $user->last_name,
                'email' => $request->email ?: $user->email,
                'role' => $request->role ?: $user->role,
                'specialization' => $request->specialization ?: $user->specialization,
                'department_id' => $request->department_id,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'pin' => $request->pin ?: $user->pin,
                'address' => $request->address ?: $user->address,
                'branch_id' => $request->branch_id ?: $user->branch_id,
                'permission' => $request->permission
            ]);
            return $user;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserInfo($request, $user, $path, $userInfo)
    {
        try {
            $userInfo->update([
                'salary' => $request->salary ?: $userInfo->salary,
                'birth_date' => $request->birth_date ?: $userInfo->birth_date,
                'gender' => $request->gender ?: $userInfo->gender,
                'nationalID' => $request->nationalID ?: $userInfo->nationalID,
                'social_situation' => $request->social_situation ?: $userInfo->social_situation,
                'level' => $request->level ?: $userInfo->level,
                'military_situation' => $request->military_situation ?: $userInfo->military_situation,
                'health_status' => $request->health_status ?: $userInfo->health_status,
                'image' => $path ?: $userInfo->image ?: $userInfo->image,
            ]);
            if ($request->role) {
                $user->assignRole($request->role);
            }
            return $userInfo;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserSalary($user, $userInfo, $request)
    {
        try {
            if ($request->salary && $userInfo->salary != $request->salary) {
                $lastSalaryUpdate = $user->salary()->latest('date')->first();
                $lastSalaryDate = $lastSalaryUpdate ? $lastSalaryUpdate->date : null;
                $monthsToCreate = $lastSalaryDate ? Carbon::parse($lastSalaryDate)
                    ->diffInMonths(Carbon::now()->format('Y-m')) : 0;
                for ($i = 0; $i < $monthsToCreate - 1; $i++) {
                    $date = Carbon::now()->subMonths($i + 1)->startOfMonth()->format('Y-m') . '-00';
                    $user->salary()->create([
                        'date' => $date,
                        'salary' => $lastSalaryUpdate ? $lastSalaryUpdate->salary : 0,
                    ]);
                }
                $user->salary()->create([
                    'user_id' => $user->id,
                    'date' => Carbon::now()->startOfMonth()->format('Y-m') . '-00',
                    'salary' => $request->salary,
                ]);
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserStudySituations($user_id, $educations)
    {
        try {
            StudySituation::where('user_id', $user_id)->delete();
            foreach ($educations as $education) {
                if (isset($education['degree']) && isset($education['study'])) {
                    StudySituation::query()->create([
                        'degree' => $education['degree'],
                        'study' => $education['study'],
                        'user_id' => $user_id,
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserCertificates($user_id, $certificates)
    {
        try {
            Certificate::where('user_id', $user_id)->delete();
            foreach ($certificates as $index => $certificate) {
                if (isset($certificate['content'])) {
                    Certificate::query()->create([
                        'user_id' => $user_id,
                        'content' => $certificate['content'],
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserLanguages($user_id, $languages)
    {
        try {
            Language::where('user_id', $user_id)->delete();
            foreach ($languages as $language) {
                if (isset($language['languages']) && isset($language['rate'])) {
                    Language::query()->create([
                        'languages' => $language['languages'],
                        'rate' => $language['rate'],
                        'user_id' => $user_id,
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserSkills($user_id, $skills)
    {
        try {
            Skills::where('user_id', $user_id)->delete();
            foreach ($skills as $skill) {
                if (isset($skill['skills']) && isset($skill['rate'])) {
                    Skills::query()->create([
                        'skills' => $skill['skills'],
                        'rate' => $skill['rate'],
                        'user_id' => $user_id,
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserFiles($user_id, $request)
    {
        try {
            AdditionalFile::where('user_id', $user_id)->delete();
            foreach ($request->additional_files as $file) {
                (function ($file) use ($user_id) {
                    $filepath = null;
                    $filepath = Files::saveFileF($file['file']);
                    $add_file = AdditionalFile::query()->create([
                        'user_id' => $user_id,
                        'description' => $file['description'],
                        'path' => $filepath,
                    ]);
                })($file);
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserExperiences($user_id, $experiences)
    {
        try {
            Career::where('user_id', $user_id)->delete();
            foreach ($experiences as $experience) {
                if (isset($experience['content'])) {
                    Career::query()->create([
                        'user_id' => $user_id,
                        'content' => $experience['content'],
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserContacts($user_id, $contacts)
    {
        try {
            Contact::where('user_id', $user_id)->delete();
            foreach ($contacts['emails'] as $contact) {
                Contact::create([
                    'user_id' => $user_id,
                    'type' => 'normal',
                    'email' => $contact['email'],
                ]);
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserPhoneNumbers($user_id, $contacts)
    {
        try {
            foreach ($contacts['phonenumbers'] as $contact) {
                Contact::create([
                    'user_id' => $user_id,
                    'type' => 'normal',
                    'phone_num' => $contact['phone_num'],
                ]);
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserEmergencyContact($user_id, $emergency_contact)
    {
        try {
            foreach ($emergency_contact as $emergency) {
                if (isset($emergency['phonenumber']) || isset($emergency['email'])) {
                    Contact::query()->create([
                        'user_id' => $user_id,
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
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserDeposits($user_id, $secretaraits)
    {
        try {
            Deposit::where('user_id', $user_id)->delete();
            foreach ($secretaraits as $secretariat) {
                if (isset($secretariat['delivery_date']) && isset($secretariat['object'])) {
                    Deposit::query()->create([
                        'user_id' => $user_id,
                        'title' => $secretariat['title'],
                        'description' => $secretariat['object'],
                        'received_date' => $secretariat['delivery_date'],
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function CreateUsercontract($id,$contract)
    {
           if(isset($contract['path']))
           {

        $path = Files::saveFileF($contract['path']);
           }
           else
           {
            $path= 'no contract';
           }
        $contract = Contract::create(
            [
                'path' => $path ,
                'startTime' => $contract['startTime'],
                'endTime' => $contract['endTime'],
                'user_id' => $id
            ]
        );
        return true;
    }
}
