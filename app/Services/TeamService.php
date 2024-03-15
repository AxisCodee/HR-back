<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\Career;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function storeTeams($request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($request) {
            $existing = Department::where('name', $request->name)->first();

            if ($existing) {
                if ($request->has('team_leader')) {
                    $oldLeader = $existing
                        ->team_leader
                        ->update(['role' => 'employee']);
                    $newLeader = User::findOrFail($request->team_leader);
                    if ($newLeader->role == 'team_leader') {
                        return ResponseHelper::error($newLeader->id . ' is a teamleader on another team');
                    }
                    $newLeader->update(['role' => 'team_leader', 'department_id' => $existing->id]);
                }
                if ($request->has('users_array')) {
                    goto addUsersLoop;
                }
                return ResponseHelper::success('Team already exists');
            }

            $existing = Department::create(['name' => $request->name, 'branch_id' => $request->branch_id]);
            $teamLeader = User::where('id', $request->team_leader)
                ->update(['role' => 'team_leader', 'department_id' => $existing->id]);
            if ($request
                ->has('users_array')
            ) {
                goto addUsersLoop;
            }
            return ResponseHelper::success('Team created successfully');

            addUsersLoop:

            foreach ($request->users_array as $user) {
                $addUser = User::where('id', $user);
                if ($addUser->role == 'team_leader') {
                    return ResponseHelper::error($addUser->id . ' is a teamleader on another team');
                }
                $addUser->update(['department_id' => $existing->id]);
            }
            return ResponseHelper::success('Team created and members added successfully');
        });
    }

    public function updateTeams($request, $id)
    {
        try {
            $request->validated();
            return DB::transaction(function () use ($request, $id) {
                $edit = Department::with('team_leader')->findOrFail($id);
                if ($request->name) {
                    if ($request->name != $edit->name) {
                        return Department::where('name', $request->name)->exists() ?
                            ResponseHelper::error('Name already exists')  : $edit->update(['name' => $request->name]);
                    }
                }
                if ($request->users_array) {
                    foreach ($request->users_array as $user) {
                        $add = User::findOrFail($user)->update(['department_id' => $id]);
                    }
                }
                if ($request->team_leader) {
                    $newLeader = User::findOrFail($request->team_leader);
                    if ($newLeader->role == 'team_leader') {
                        return ResponseHelper::error($newLeader->id . ' is a teamleader on another team');
                    }
                    $newLeader->update(['role' => 'team_leader', 'department_id' => $id]);
                    $oldLeader = $edit->team_leader->update(['role' => 'employee']);
                    Career::create([
                        'user_id' => $edit->id,
                        'content' => 'worked as a teamleader',
                    ]);
                }
                return ResponseHelper::success('Members added & Team updated successfully');
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e);
        }
    }


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
}
