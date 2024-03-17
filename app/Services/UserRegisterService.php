<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Http\Traits\Files;
use App\Models\AdditionalFile;
use App\Models\Career;
use App\Models\Certificate;
use App\Models\Contact;
use App\Models\Deposit;
use App\Models\Language;
use App\Models\Skills;
use App\Models\StudySituation;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\UserSalary;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserRegisterService
{

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

}
