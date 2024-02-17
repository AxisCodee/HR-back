<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\ContactRequest;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Career;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Role;
use App\Services\RoleService;
use App\Services\TeamService;
use App\Services\UserServices;
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

    public function __construct(RoleService $roleService,TeamService $teamService,UserServices $userService)
    {
        $this->roleService = $roleService;
        $this->teamService = $teamService;
        $this->userService = $userService;

    }

    //get all users info
    public function all_users(Request $request)
    {
        $all_users = User::query()->where('branch_id', $request->branch_id)
            ->with('department', 'userInfo:id,user_id,image')->whereNotNull('department_id')->get()->toArray();
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
                'absences'
            )->get()->toArray();
        return ResponseHelper::success($spec_user, null, 'user info returned successfully', 200);
    }
    //edit a specific user info by his ID
    public function edit_user(UpdateUserRequest $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $spec_user = User::findOrFail($id);
            if ($spec_user->role != $request->role) {
                $add_exp = Career::create([
                    'user_id' => $id,
                    'content' => 'worked as a ' . $spec_user->role,
                ]);
            }
            $spec_user->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'role'    => $request->role,
                'department_id' => $request->department_id,
            ]);
            return ResponseHelper::success($spec_user, null, 'user info updated successfully', 200);
        });
        return ResponseHelper::error('Error', null);
    }
    //remove a user from a team
    public function removeFromTeam($id)
    {
       $result= $this->teamService->remove_from_team($id);
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
        $result= $this->teamService->getTeams($branchId);
        return $result;

    }
    //add members to a team
    public function Addmembers(Request $request, $team)
    {
      $result =  $this->teamService->addMembers($request, $team);
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
    public function new_contact(ContactRequest $request)
    {
        $validate = $request->validated();
        $new_contact = Contact::create([
            'user_id' => $validate['user_id'],
            'type'    => $validate['type'],
            'contact' => $validate['contact'],
        ]);
        return ResponseHelper::created($new_contact, 'contact added successfully');
    }
    //edit contact of a user
    public function edit_contact($id, ContactRequest $request)
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
}
