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
}
