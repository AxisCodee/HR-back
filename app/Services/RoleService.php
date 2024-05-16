<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;

class RoleService
{
    public function roleHierarchy()
    {
        $admins = User::where('role', 'admin')->with('userInfo')->first();
        $managers = User::where('role', 'project_manager')->with('userInfo')->get()->toArray();
        $leaders = User::where('role', 'team_leader')->with('my_team')->get();
        $teamMembers = $leaders->map(function ($leader) {
            $leaderData = $leader->toArray();
            unset ($leaderData['my_team']);
            return [
                'leader' => $leaderData,
                'image' => $leader->userInfo ? $leader->userInfo->image : null,
                'Level3' => $leader->my_team->map(function ($member) {
                    return [
                        'member' => $member,
                        'image' => $member->userInfo ? $member->userInfo->image : null,
                    ];
                })
            ];
        });
        $response = [
            'CEO' => $admins,
            'Level1' => $managers,
            'level2' => $teamMembers,
        ];

        return ResponseHelper::success(
            [$response],
            null,
            'Roles hierarchy returned successfully',
            200
        );
    }

    public function userProf($request)
    {
        $levels = ["Junior", "Mid", "Senior"];
        $specialisation = ["UI-UX", "Front-End", "Back-End", "Mobile", "Graphic-Design",
            "Project-Manager", "Traineeship"];
        $departments = Department::query()->where('branch_id', $request->branch_id)
            ->get()->toArray();

        return ResponseHelper::success(
            [
                'levels' => $levels,
                'specialisation' => $specialisation,
                'departments' => $departments,
            ],
            "Professional selects returned successfully",
            200
        );
    }


    public function allDepRul()
    {
        $departments = Department::query()->get()->toArray();
        $roles = Role::query()->get()->toArray();
        return ResponseHelper::success(
            [
                'Departments' => $departments,
                'Roles' => $roles,
            ],
            null,
            'Departments and roles returned successfully',
            200
        );
    }
}
