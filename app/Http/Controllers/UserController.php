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

    public function __construct(RoleService $roleService, TeamService $teamService, UserServices $userService)
    {
        $this->roleService = $roleService;
        $this->teamService = $teamService;
        $this->userService = $userService;
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
                'absences'
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




    public function addTeams(StoreTeamRequest $request)
    {
        $existingDepartment = Department::where('name', $request->name)
        ->where('branch_id', $request->branch_id)
        ->first();

    if ($existingDepartment) {
        return ResponseHelper::error('The department already exists in the specified branch');
    }
        $department = Department::create([
            'name' => $request->name,
            'branch_id'=>$request->branch_id
        ]);

        foreach ($request->users as $userId) {
            $addUser = User::find($userId);
            if ($addUser) {
                $addUser->department_id = $department->id;
                $addUser->update([
                    'role' => 'employee'
                ]);
            }
        }

        $leader = $request->team_leader;
        $teamLeader = User::where('id', $leader)
        ->where('role', '!=', 'team_leader')->first();

        if (!$teamLeader) {
            return ResponseHelper::error('You cannot add a team leader to another team');
        }

        $teamLeader->update([
            'role' => 'team_leader',
            'department_id' => $department->id
        ]);

        return ResponseHelper::success('Team added successfully');
    }

public function updateTeam($id,Request $request){

    $department=Department::query()
    ->where('id',$id)
    ->update([
        'name'=>$request->name,
    ]);
    User::where('department_id',$id)
    ->update(['department_id'=>null]);

    foreach ($request->users as $userId) {
        $addUser = User::find($userId);
        if ($addUser) {
            $addUser->department_id = $id;
            $addUser->update([
                'role' => 'employee'
            ]);
        }
    }
     User::where('department_id',$id)
    ->where('role','team_leader')
    ->update(['role'=>'employee']);

    $leader = $request->team_leader;
    $teamLeader = User::where('id', $leader)
    ->where('role', '!=', 'team_leader')->first();

    if (!$teamLeader) {
        return ResponseHelper::error('You cannot add a team leader to another team');
    }

    $teamLeader->update([
        'role' => 'team_leader',
        'department_id' => $id
    ]);

    return ResponseHelper::success('Team added successfully');


}









}
