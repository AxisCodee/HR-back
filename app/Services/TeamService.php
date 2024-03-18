<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\Career;
use App\Models\Department;
use App\Models\DepartmentParent;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function remove_from_team($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }
            if ($user->role == 'team_leader') {
                return ResponseHelper::error('you cant remove a team leader from his team');
            }
            $remove = $user->update(['department_id' => null]);
            return ResponseHelper::success('User removed from team successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to remove user from team', $e->getCode());
        }
    }

    public function addMembers($request, $team)
    {
        try {
            return DB::transaction(function () use ($request, $team) {
                foreach ($request->users_array as $user) {
                    $add = User::findOrFail($user);
                    if ($add->role == 'team_leader') {
                        return ResponseHelper::error($user . ' is a teamleader on another team');
                    }
                    $add->update(['department_id' => $team]);
                }
                return ResponseHelper::success('users added to the team successfully');
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to add users to the team', $e->getCode());
        }
    }

    public function getTeams($branchId)
    {
        $departments = Department::query()->where('branch_id', $branchId)
            ->with('user', 'user.userInfo')
            ->get()
            ->toArray();
        return ResponseHelper::success($departments);
    }


    public function showTeams($branchId)
    {
        $departments = Department::query()->where('branch_id', $branchId)
            ->get()
            ->toArray();
        return ResponseHelper::success($departments);
    }


//add team
    public function addTeams($request)
    {
        try {
            DB::beginTransaction();
            //transaction
            $existingDepartment = Department::where('name', $request->name)
                ->where('branch_id', $request->branch_id)
                ->first();
            //check if department exist firstly
            if ($existingDepartment) {
                //throw exception if department exist
                throw new Exception('The department already exists in the specified branch');
            }
            //else create department with name
            $department = Department::create([
                'name' => $request->name,
                'branch_id' => $request->branch_id,
            ]);
            if ($request->parent_id) {
                Department::query()->where('id',$department->id)->update(
                    [
                        'parent_id' => $request->parent_id,
                    ]
                );
            }
            //if request has team_leader => find it or fail
            if ($request->team_leader) {
                $leader = $request->team_leader;
                $teamLeader = User::where('role', '!=', 'admin')
                    ->findOrFail($leader);
                //check if team_leader exist in another team to throw exception
                if (!$teamLeader || $teamLeader->role == 'team_leader') {
                    throw new Exception('You cannot add a team leader to another team');
                }
                //else set role => team_leader and set department_id
                $teamLeader->update([
                    'role' => 'team_leader',
                    'department_id' => $department->id
                ]);
            }
            //add array of users to team with role employee
            if ($request->users_array) {
                foreach ($request->users_array as $userId) {
                    $addUser = User::findOrFail($userId)->where('role', '!=', 'admin');
                    if ($addUser) {
                        $addUser->where('id', $userId)->update([
                            'role' => 'employee',
                            'department_id' => $department->id,
                        ]);
                    }
                }
            }
            DB::commit();
            return 'Team added successfully';
        } catch (Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }


//update team
    public function updateTeam($department, $request)
    {
        DB::beginTransaction(); //transaction
        try {
            $updateDepartment = Department::query()
                ->where('id', $department->id)
                ->update([
                    'name' => $request->name,
                ]);
            //update department (team)
            if ($request->parent_id) {
                $DepartmentParent = Department::query()->where('department_id', $department->id)
                    ->update(
                        [
                            'parent_id' => $request->parent_id
                        ]
                    );
            }
            User::where('department_id', $department->id)
                ->update(['department_id' => null]);
            //set department_id null for all user
            User::where('department_id', $department->id)
                ->where('role', 'team_leader')
                ->update(['role' => 'employee']);
            // set role employee for team leader to reset roles for all department
            if ($request->users_array) { //store many users in team as array
                foreach ($request->users_array as $userId) {
                    $addUser = User::findOrFail($userId)->where('role', '!=', 'admin');
                    if ($addUser) {
                        $addUser->where('id', $userId)->update([
                            'role' => 'employee',
                            'department_id' => $department->id

                        ]); // store users as employees
                    }
                }
            }
            // $leader = $request->team_leader;
            // $teamLeader = User::where('id', $leader)->where('role','!=','admin')
            //     ->first(); // team leader

            // if (!$teamLeader) { //exception if the team leader is exist in another team
            //     throw new Exception('You cannot add a team leader to another team');
            // }
            //set team leader
            // $teamLeader->update([
            //     'role' => 'team_leader',
            //     'department_id' => $department->id
            // ]);

            DB::commit();//commit
            return 'Team added successfully';
        } catch (Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    // public function showTree()
    // {
    //     $childs = Department::with('user')->with('child.department.user')->get();
    //     $tree = [];
    //     foreach ($childs as $department) {
    //         $tree[] = $this->buildTree($department);
    //     }
    //     return $tree;}
    // public function getTree()
    // {
    //     $childs = Department::with('user')->with('child')->get();
    //     $tree = [];
    //     foreach ($childs as $department) {
    //         $tree[] = $this->buildTree($department);
    //     }
    //     return $tree;
    // }

    // public function buildTree($department)
    // {
    //     $tree = $department->toArray();
    //     $childDepartments = $department->child;
    //     if ($childDepartments) {
    //         $tree['child'] = [];

    //         foreach ($childDepartments as $childDepartment) {
    //             $tree['child'][] = $this->buildTree($childDepartment);
    //         }
    //     }
    //     return $tree;
    // }

//     public function getTree($parentId = null)
// {
//     $departments = DepartmentParent::with('department')->where('parent_id', $parentId)->get();
//     $tree = [];

//     foreach ($departments as $department) {
//         $tree[] = [
//             'department' => $department,
//             'child' => $this->getTree($department->id),
//         ];
//     }

//     return $tree;
// }



// public function getTree($parentId = null)
// {
//     $tree = $this->buildTree($parentId);


// }


// public function buildTree($parentId = null)
// {
//     $tree = [];
//     $departments = DepartmentParent::with('department')->where('parent_id', $parentId)->get();

//     foreach ($departments as $department) {
//         $childTree = $this->buildTree($department->department_id);

//         if (!empty($childTree)) {
//             $departmentData = $department->department->toArray();
//             $departmentData['child'] = $childTree;
//             $tree[] = $departmentData;
//         } else {
//             $tree[] = $department->department->toArray();
//         }
//     }

//     return $tree;
// }

public function getTree()
{
    $rootDepartments = Department::whereNull('parent_id')->with('user')->get();
    $tree = [];

    foreach ($rootDepartments as $department) {
        $tree[] = $this->buildTree($department);
    }

    return $tree;
}

public function buildTree($department)
{
    
    $tree = $department->toArray();


    $childDepartments = $department->child;

    if ($childDepartments) {
        $tree['child'] = [];



        foreach ($childDepartments as $childDepartment) {
            $tree['child'][] = $this->buildTree($childDepartment);


        }
    }




    return $tree;
}

}

