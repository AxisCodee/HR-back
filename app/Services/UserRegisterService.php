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
use App\Models\UserSalary;
use App\Models\Certificate;
use App\Helper\ResponseHelper;
use App\Models\AdditionalFile;
use App\Models\StudySituation;
use Illuminate\Support\Facades\Hash;

class UserRegisterService
{
    /**
     * User Register Methods
     */
    protected $fileService;


    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function createUser($request, $department_id, $branch_id)
    {
        $newPin = User::query()->latest()->value('pin') + 1;
        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
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
    }

    public function createUserInfo($request, $user, $path)
    {
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
    }

    public function createUserSalary($user, $userInfo)
    {
        $userSalary = UserSalary::query()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m') . '-00',
            'salary' => $userInfo->salary
        ]);
        return $userSalary;
    }

    public function createUserStudySituations($user, $educations)
    {
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
    }

    public function createUserCertificates($user, $certificates)
    {
        foreach ($certificates as $index => $certificate) {
            if (isset($certificate['content'])) {
                Certificate::query()->create([
                    'user_id' => $user->id,
                    'content' => $certificate['content'],
                ]);
            }
        }
        return true;
    }

    public function createUserLanguages($user, $languages)
    {
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
    }

    public function createUserSkills($user, $skills)
    {
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
    }

    public function createUserFiles($user, $request)
    {
        foreach ($request->additional_files as $file) {
            (function ($file) use ($user, $request) {
                $filepath = $this->fileService->upload($file['file'], 'file');
                AdditionalFile::query()->create([
                    'user_id' => $user->id,
                    'description' => $file['description'],
                    'path' => $filepath,
                ]);
            })($file);
        }
        return true;
    }

    public function createUserExperiences($user, $experiences)
    {
        foreach ($experiences as $experience) {
            if (isset($experience['content'])) {
                Career::query()->create([
                    'user_id' => $user->id,
                    'content' => $experience['content'],
                ]);
            }
        }
        return true;
    }

    public function createUserContacts($user, $contacts)
    {
        foreach ($contacts['emails'] as $contact) {
            Contact::create([
                'user_id' => $user->id,
                'type' => 'normal',
                'email' => $contact['email'],
            ]);
        }
        return true;
    }

    public function createUserPhoneNumbers($user_id, $contacts)
    {
        foreach ($contacts['phonenumbers'] as $contact) {
            Contact::create([
                'user_id' => $user_id,
                'type' => 'normal',
                'phone_num' => $contact['phone_num'],
            ]);
        }
        return true;
    }

    public function createUserEmergencyContact($user_id, $emergency_contact)
    {
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
    }

    public function createUserDeposits($user_id, $secretaraits)
    {
        foreach ($secretaraits as $secretary) {
            if (isset($secretary['delivery_date']) && isset($secretary['object'])) {
                $path = $this->fileService->upload($secretary['path'], 'file');
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
    }

    /**
     * User Update Methods
     */

    public function updateUser($request, $user)
    {
        $user->update([
            'first_name' => $request->first_name ?: $user->first_name,
            'middle_name' => $request->middle_name,
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
    }

    public function updateUserInfo($request, $user, $path, $userInfo)
    {
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
    }

    public function updateUserSalary($user, $userInfo, $request)
    {
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
    }

    public function updateUserStudySituations($user_id, $educations)
    {
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
    }

    public function updateUserCertificates($user_id, $certificates)
    {
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
    }

    public function updateUserLanguages($user_id, $languages)
    {
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
    }

    public function updateUserSkills($user_id, $skills)
    {
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

    }

    public function updateUserFiles($user_id, $request)
    {
        AdditionalFile::where('user_id', $user_id)->delete();
        foreach ($request->additional_files as $file) {
            (function ($file) use ($user_id) {
                $filepath = $this->fileService->upload($file['file'], 'file');
                AdditionalFile::query()->create([
                    'user_id' => $user_id,
                    'description' => $file['description'],
                    'path' => $filepath,
                ]);
            })($file);
        }
        return true;
    }

    public function updateUserExperiences($user_id, $experiences)
    {

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
    }

    public function updateUserContacts($user_id, $contacts)
    {
        Contact::where('user_id', $user_id)->delete();
        foreach ($contacts['emails'] as $contact) {
            Contact::create([
                'user_id' => $user_id,
                'type' => 'normal',
                'email' => $contact['email'],
            ]);
        }
        return true;
    }

    public function updateUserPhoneNumbers($user_id, $contacts)
    {
        foreach ($contacts['phonenumbers'] as $contact) {
            Contact::create([
                'user_id' => $user_id,
                'type' => 'normal',
                'phone_num' => $contact['phone_num'],
            ]);
        }
        return true;
    }

    public function updateUserEmergencyContact($user_id, $emergency_contact)
    {
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
    }

    public function updateUserDeposits($user_id, $secretaraits)
    {
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
    }

    public function CreateUsercontract($id, $contract)
    {
        if (isset($contract['path'])) {
            $path = $this->fileService->upload($contract['path'], 'file');
        } else {
            $path = 'no contract';
        }
        if (isset($contract['startTime'], $contract['endTime'])) {
            Contract::create(
                [
                    'path' => $path,
                    'startTime' => $contract['startTime'],
                    'endTime' => $contract['endTime'],
                    'user_id' => $id
                ]
            );
        }
        return true;
    }
}
