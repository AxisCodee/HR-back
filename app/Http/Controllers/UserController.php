<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\ContactRequest\StoreContactRequest;
use App\Http\Requests\ContactRequest\UpdateContactRequest;
use App\Http\Requests\TeamRequest\StoreTeamRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Models\Policy;
use App\Models\UserInfo;
use App\Services\FileService;
use App\Services\UserRegisterService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Models\Department;
use App\Services\RoleService;
use App\Services\TeamService;
use App\Services\UserServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\once;

class UserController extends Controller
{
    private $roleService;
    private $teamService;
    protected $userService;
    public $userRegisterService;
    public $fileService;

    public function __construct(
        RoleService         $roleService,
        TeamService         $teamService,
        UserServices        $userService,
        UserRegisterService $userRegisterService,
        FileService         $fileService,
    )
    {
        $this->roleService = $roleService;
        $this->teamService = $teamService;
        $this->userService = $userService;
        $this->userRegisterService = $userRegisterService;
        $this->fileService = $fileService;
    }

    //get all users info
    public function all_users(Request $request)
    {
        $policy = Policy::where('branch_id', $request->branch_id)->first();
        $totalWorkingHours = $policy?->getTotalWorkingHours($request->date) ?? 0;
        User::setTotalWorkingHours($totalWorkingHours);
        User::setOldSalary($policy);
        $all_users = User::query()
            ->where('branch_id', $request->branch_id)
            ->whereNot('role', 'admin')
            ->with('department', 'userInfo')
            ->whereNull('deleted_at')
            ->get()
            ->toArray();
        return ResponseHelper::success($all_users, null, 'all users info returned successfully', 200);
    }


    public function resignedusers(Request $request) //return users without departments
    {
        $all_users = User::query()->where('branch_id', $request->branch_id)
            ->onlyTrashed()
            ->with('userInfo:id,user_id,image')
            ->get()
            ->toArray();
        return ResponseHelper::success($all_users, null, 'all resigned users', 200);
    }

    public function allAndTrashUser(Request $request)
    {
        $branch_id = $request->branch_id;
        $all_users = User::query()
            ->where('branch_id', $branch_id)
            ->with('userInfo:id,user_id,image', 'department', 'contract:id,user_id,startTime,endTime')
            ->withTrashed()->get();
        $usersWithStatus = collect($all_users)->map(function ($user) {
            $userArray = $user->toArray();
            $userArray['state'] = $user->trashed() ? 'Former' : 'Active';
            return $userArray;
        });
        return ResponseHelper::success($usersWithStatus, null, 'All users (including trashed)', 200);
    }


    //get a specific user by the ID
    public function specific_user($id)
    {
        $spec_user = User::query()
            ->where('id', $id)
            ->with(
                'userInfo',
                'department',
                'contract',
                'my_files',
                'my_contacts',
                'careers',
                'deposits',
                'notes',
                'certificates',
                'languages',
                'study_situations',
                'emergency',
                // 'absences',
                'skills',
                'phoneNumber',
                'emails'
            )->get()->toArray();
        return ResponseHelper::success($spec_user, null, 'user info returned successfully', 200);
    }

    //edit a specific user info by his ID
    public function edit_user(UpdateUserRequest $request, $id)
    {
        $result = $this->userService->editUser($request, $id);
        return ResponseHelper::success($result, null, 'user info updated successfully');
    }

    public function updateAdmin(Request $request)
    {
        $user = Auth::user();
        if ($user->role == 'admin') {
            $result = $this->userService->updateAdmin($user, $request);
            return ResponseHelper::success($result, null, 'Updated successfully');
        }
        return ResponseHelper::error('not authorized');

    }

    //remove a user from a team
    public function removeFromTeam($id)
    {
        $result = $this->teamService->remove_from_team($id);
        return $result;
    }

    //delete a specific user by his id
    public function remove_user($user)
    {
        $remove_user = User::findOrFail($user);
        $before = $remove_user->department_id;
        $remove_user->delete();
        $remove_user->update(['department_id', $before]);
        return ResponseHelper::success('user removed successfully');
    }

    //get all teams with their users
    public function getTeams(Request $request)
    {
        $branchId = $request->branch_id;
        $result = $this->teamService->getTeams($branchId);
        return $result;
    }


    public function showTeams(Request $request)
    {
        $branchId = $request->branch_id;
        $result = $this->teamService->showTeams($branchId);
        return $result;
    }

    //add members to a team
    public function Addmembers(Request $request, $team)
    {
        $result = $this->teamService->addMembers($request, $team);
        return $result;
    }


    //delete an exisiting team
    public function deleteTeam($id)
    {
        DB::beginTransaction();
        $department = Department::findOrFail($id);
        User::where('department_id', $id)->update([
            'department_id' => null,
            'role' => null
        ]);
        Department::where('parent_id', $id)->update([
            'parent_id' => null
        ]);
        $department->delete();
        DB::commit();
        return ResponseHelper::success('Team deleted successfully');

    }

    //get all members of a team
    public function getMemberOfTeam(Department $department)
    {
        $members = $department->users()->get()->toArray();
        return ResponseHelper::success($members);
    }

    //add new contact to a user
    public function new_contact(StoreContactRequest $request)
    {
        $validate = $request->validated();
        $new_contact = Contact::create([
            'user_id' => $validate['user_id'],
            'type' => $validate['type'],
            'contact' => $validate['contact'],
        ]);
        return ResponseHelper::created($new_contact, 'contact added successfully');
    }

    //edit contact of a user
    public function edit_contact($id, UpdateContactRequest $request)
    {
        $validate = $request->validated();
        $edit = Contact::findOrFail($id);
        $edited = $edit->update($validate);
        return ResponseHelper::updated($edit, 'contact edited successfully');
    }

    //delete contact of a user
    public function delete_contact($id)
    {
        $delete = Contact::findOrFail($id)->delete();
        return ResponseHelper::deleted('contact deleted successfully');
    }

    //get all departments and rules
    public function all_dep_rul()
    {
        $result = $this->roleService->allDepRul();
        return $result;
    }

    //get roles hierarchy
    public function roleHierarchy()
    {
        $result = $this->roleService->roleHierarchy();
        return $result;
    }

    public function user_prof(Request $request)
    {
        $result = $this->roleService->userProf($request);
        return $result;
    }

    public function not_admin(Request $request)
    {
        $branch_id = $request->input('branch_id');
        $notadmin = $this->userService->except_admins($branch_id);
        return ResponseHelper::success($notadmin, 'all users that are not admin returned successfuly');
    }


    public function updateUser(User $user, Request $request)
    {
        $user = $this->userRegisterService->updateUser($request, $user);
        $path = null;
        if ($request->image) {
            $path = $this->fileService->upload($request->image, 'image');
        }
        $userInfo = UserInfo::where('user_id', $user->id)->first();
        $this->userRegisterService->updateUserSalary($user, $userInfo, $request);
        $this->userRegisterService->updateUserInfo($request, $user, $path, $userInfo);
        $educations = $request->educations;
        $certificates = $request->certificates;
        $languages = $request->languages;
        $skills = $request->skills;
        $experiences = $request->experiences;
        $contacts = $request->contacts;
        $secretaraits = $request->secretaraits;
        $emergency_contact = $request->emergency_contact;
        if ($educations) {
            $this->userRegisterService->updateUserStudySituations($user->id, $educations);
        }
        if ($certificates) {
            $this->userRegisterService->updateUserCertificates($user->id, $certificates);
        }
        if ($languages) {
            $this->userRegisterService->updateUserLanguages($user->id, $languages);
        }
        if ($skills) {
            $this->userRegisterService->updateUserSkills($user->id, $skills);
        }
        if ($request->additional_files) {
            $this->userRegisterService->updateUserFiles($user->id, $request);
        }
        if ($experiences) {
            $this->userRegisterService->updateUserExperiences($user->id, $experiences);
        }
        if (isset($contacts['emails'][0])) {
            $this->userRegisterService->updateUserContacts($user->id, $contacts);
        }
        if (isset($contacts['phonenumbers'])) {
            $this->userRegisterService->updateUserPhoneNumbers($user->id, $contacts);
        }
        if ($request->emergency_contact) {
            $this->userRegisterService->updateUserEmergencyContact($user->id, $emergency_contact);
        }
        if ($secretaraits) {
            $this->userRegisterService->updateUserDeposits($user->id, $secretaraits);
        }
        return ResponseHelper::success("Updated");

    }

    //add team
    public function addTeams(StoreTeamRequest $request)
    {
        $result = $this->teamService->addTeams($request);
        return ResponseHelper::success($result);
    }


    //update team
    public function updateTeam(Department $department, Request $request)
    {
        $result = $this->teamService->updateTeam($department, $request);
        return ResponseHelper::success($result);
    }

    public function getTree(Request $request)
    {
        $result = $this->teamService->getTree($request);
        return ResponseHelper::success($result);
    }

    public function Tree(Request $request)
    {
        return $this->teamService->getTree($request);
    }

    public function GetAbsenceTypes(Request $request)
    {
        return $this->userService->AllAbsenceTypes($request);
    }

    public function Users_array(Request $request)
    {
        $request->validate([
            'users' => ['required', 'array'],
            'users.*' => ['required', 'integer', 'exists:users,id', 'min:1']
        ]);
        return $this->userService->usersarray($request->users);
    }


    public function updatePassword(Request $request)
    {
        User::where('id', $request->id)->update(
            [
                'password' => Hash::make($request->password)

            ]
        );

    }

}
