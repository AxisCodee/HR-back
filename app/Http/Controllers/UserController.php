<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\ContactRequest\StoreContactRequest;
use App\Http\Requests\ContactRequest\UpdateContactRequest;
use App\Http\Requests\TeamRequest\StoreTeamRequest;
use App\Http\Requests\TeamRequest\UpdateTeamRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Models\Career;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Role;
use App\Services\RoleService;
use App\Services\TeamService;
use App\Services\UserServices;
use App\Services\EditUserService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use TADPHP\TAD;
use TADPHP\TADFactory;
use Illuminate\Support\Facades\Validator;

require 'tad\vendor\autoload.php';

class UserController extends Controller
{



    private $roleService;
    private $teamService;
    protected $userService;
    protected $editUserService;

    public function __construct(RoleService $roleService, TeamService $teamService, UserServices $userService,EditUserService $editUserService )
    {
        $this->roleService = $roleService;
        $this->teamService = $teamService;
        $this->userService = $userService;
        $this->editUserService = $editUserService;
    }

    //get all users info
    public function all_users(Request $request)
    {
        $all_users = User::query()->where('branch_id', $request->branch_id)
            ->with('department', 'userInfo:id,user_id,image')->whereNull('deleted_at')->get()->toArray();
        return ResponseHelper::success($all_users, null, 'all users info returned successfully', 200);
    }

    public function usersWithoutDepartment(Request $request) //return users without departments
    {
        $all_users = User::query()->where('branch_id', $request->branch_id)
            ->where('department_id', null)
            ->with('userInfo:id,user_id,image')->get()->toArray();
        return ResponseHelper::success($all_users, null, 'all users without departments', 200);
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
                'absences',
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
        return $result;
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
        $remove_user = User::findOrFail($user)->delete();
        return ResponseHelper::deleted('user removed successfully');
    }
    //get all teams with their users
    public function getTeams(Request $request)
    {
        $branchId = $request->branch_id;
        $result = $this->teamService->getTeams($branchId);
        return $result;
    }
    //add members to a team
    public function Addmembers(Request $request, $team)
    {
        $result = $this->teamService->addMembers($request, $team);
        return $result;
    }

    //add new team and add users to it
    public function storeTeams(StoreTeamRequest $request)
    {
        $result = $this->teamService->storeTeams($request);
        return $result;
    }

    //update an existing team name
    public function updateTeams(UpdateTeamRequest $request, $id)
    {
        $result = $this->teamService->updateTeams($request, $id);
        return $result;
    }
    //delete an exisiting team
    public function deleteTeam($id)
    {
        try {
            $remove = Department::findOrFail($id)->delete();
            return ResponseHelper::deleted('team deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Team does not exist');
        }
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

    public function user_prof()
    {
        $result = $this->roleService->userProf();
        return $result;
    }

    public function not_admin(Request $request)
    {
        $branch_id =  $request->input('branch_id');
        $notadmin =  $this->userService->except_admins($branch_id);
        return ResponseHelper::success($notadmin, 'all users that are not admin returned successfuly');
    }





    public function updateUser(User $user,Request $request)
    {
        try {
        $result = $this->editUserService->updateUser($user,$request);
        return ResponseHelper::success($result,'User update successfully');
    } catch (\Illuminate\Validation\ValidationException $e) {
        return ResponseHelper::error($e->validator->errors()->first(), 400);
    }
    catch (\Exception $e) {
        return ResponseHelper::error($e->getMessage(), $e->getCode());
    }
    }



//add team
    public function addTeams(StoreTeamRequest $request)
    {
        $result = $this->teamService->addTeams($request);
        return ResponseHelper::success($result);
    }


//update team
    public function updateTeam($id, Request $request)
    {
        $result = $this->teamService->updateTeam($id, $request);
        return ResponseHelper::success($result);
    }







}
