<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\ContactRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Career;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use TADPHP\TAD;
use TADPHP\TADFactory;
use Illuminate\Support\Facades\Validator;

require 'tad\vendor\autoload.php';

class UserController extends Controller
{

    //get all users info
    public function all_users()
    {
        $all_users = User::query()->get()->toArray();
        return ResponseHelper::success($all_users, null, 'all users info returned successfully', 200);
    }
    //get a specific user by the ID
    public function specific_user($id)
    {
        $spec_user = User::findOrFail($id);
        return ResponseHelper::success($spec_user, null, 'user info returned successfully', 200);
    }
    //edit a specific user info by his ID
    public function edit_user(UpdateUserRequest $request, $id)
    {
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
    }
    //remove a user from a team
    public function remove_from_team($id)
    {
        $remove = User::query()
            ->where('id', $id)
            ->update(['department_id' => null]);

        return ResponseHelper::success('user removed from team successfully');
    }
    //delete a specific user by his id
    public function remove_user($id)
    {
        $remove_user = User::findOrFail($id)->delete();
        return ResponseHelper::deleted('user removed successfully');
    }
    //get all teams with their users
    public function getTeams()
    {
        $department = Department::query()
            ->with('user')
            ->get()
            ->toArray();
        return ResponseHelper::success($department);
    }
    //add members to a team
    public function Addmembers(Request $request, $team)
    {
        foreach ($request->users_array as $user) {
            $add = User::findOrFail($user);
            $add->department_id = $team;
            $add->save();
        }
        return ResponseHelper::created('users added to the team successfully');
    }

    //add new team and add users to it
    public function storeTeams(Request $request)
    {
        $team = Department::updateOrCreate(['name' => $request->name]);
        if ($request->users_array) {
            foreach ($request->users_array as $user) {
                $update = User::where('id', $user)->first();
                $update->department_id = $team->id;
                $update->save();
            }
            return ResponseHelper::created('users added to the team successfully');
        }

        $teamLeader = User::query()
            ->where('id', $request->team_leader)
            ->update([
                'role' => 'Team_Leader'
            ]);
        return ResponseHelper::created('team added successfully');
    }
    //update an existing team name
    public function updateTeams(Request $request, $id)
    {
        $edit = Department::findOrFail($id);
        $edited = $edit->update([
            'name' => $request->name,
        ]);
        return ResponseHelper::updated($edit, 'team updated successfully');
    }
    //delete an exisiting team
    public function deleteTeam($id)
    {
        $remove = Department::findOrFail($id)->delete();
        return ResponseHelper::deleted('team deleted successfully');
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
        $departments = Department::query()->get()->toArray();
        $roles = Role::query()->get()->toArray();
        return ResponseHelper::success(
            [
                'Departments' => $departments,
                'Roles' => $roles,
            ],
            null,
            'departments and roles returned successfully',
            200
        );
    }
    //get roles hierarchy
    public function roleHierarchy()
    {
        $admins = User::where('role', 'admin')->with('userInfo')->first();
        $managers = User::where('role', 'project_manager')->with('userInfo')->get()->toArray();
        $leaders = User::where('role', 'team_leader')->with('my_team')->get();
        $teamMembers = $leaders->map(function ($leader) {
            $leaderData = $leader->toArray();
            unset($leaderData['my_team']);
            return
                [
                    'leader' => $leaderData,
                    'image' => $leader->userInfo ? $leader->userInfo->image : null,
                    'Level3' => $leader->my_team->map(function ($member) {
                        return [
                           'member'=> $member,
                            'image' => $member->userInfo ? $member->userInfo->image : null,
                        ];
                    })
                ];
        });
        $response =[
          'CEO' => $admins,
        'Level1' => $managers,
        'level2' => $teamMembers,];
        return ResponseHelper::success(

             [ $response]
            ,
            null,
            'Roles hierarchy returned successfully',
            200
        );
    }

    public function user_prof()
    {
        $role = ["Junior","Mid","Senior"];
        $specialisation = ["UI-UX","Front-End","Back-End","Mobile","Graphic-Desgin","Project-Manager"];
        $department = Department::query()->get()->toArray();

        return ResponseHelper::success(
            [
                'role'=> $role,
                'specialisation'=>$specialisation,
                'departments'=>$department,
            ]
        ,"Professional selects returned successfully",200);
    }
}


