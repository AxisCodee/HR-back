<?php

namespace App\Services;

use App\Helper\ResponseHelper;
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
                    $newLeader = User::findOrFail($request->team_leader)
                        ->update(['role' => 'team_leader', 'department_id' => $existing->id]);
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
            ->has('users_array')) {
                goto addUsersLoop;
            }
            return ResponseHelper::success('Team created successfully');

            addUsersLoop:

            foreach ($request->users_array as $user) {
                $addUser = User::where('id', $user)
                ->update(['department_id' => $existing->id]);
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
                        return Department::where('name', $request->name)->exists() ? ResponseHelper::error('Name already exists')  : $edit->update(['name' => $request->name]);
                    }
                }
                if ($request->users_array) {
                    foreach ($request->users_array as $user) {
                        $add = User::findOrFail($user)->update(['department_id' => $id]);
                    }
                    if ($request->team_leader) {
                        $oldLeader = $edit->team_leader->update(['role' => 'employee']);
                        $newLeader = User::findOrFail($request->team_leader)
                            ->update(['role' => 'team_leader', 'department_id' => $id]);
                    }
                    return ResponseHelper::success('Members added & Team updated successfully');
                }
            });
            return ResponseHelper::success('Team updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e);
        }
    }



}
