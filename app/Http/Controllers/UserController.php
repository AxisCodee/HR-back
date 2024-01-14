<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use TADPHP\TAD;
use TADPHP\TADFactory;
require 'tad\vendor\autoload.php';

class UserController extends Controller
{

//get all users info
    public function all_users()
    {
        $all_users = User::query()->get(['id','first_name','last_name'])->toArray();

        return ResponseHelper::success($all_users, null, 'all users info returned successfully', 200);
    }
//get a specific user by the ID
    public function specific_user($id)
    {
        $spec_user = User::findOrFail($id);
        return ResponseHelper::success($spec_user, null, 'user info returned successfully', 200);
    }
//edit a specific user info by his ID
    public function edit_user(Request $request)
    {
        $spec_user = User::findOrFail($request->id);
        $spec_user->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => $request->password,
            'role_id'    => $request->role_id,
            'department_id' => $request->department_id,
        ]);
        return ResponseHelper::success($spec_user, null, 'user info updated successfully', 200);
    }
//delete a specific usre by his id
    public function remove_user($id)
    {
        $remove_user = User::findOrFail($id)->delete();
        return ResponseHelper::success(null, null, 'user removed successfully', 200);
    }
    public function getTeams(){
       $department= Department::query()->get();
       foreach($department as $item)
       {
        $departmentName=$department->name;
        $departmentId=$department->id;
        $count=$department->users()->count();

        $results[]=$result=['teamName'=>$departmentName,
        'countOfMember'=> $count
       ];



        return ResponseHelper::success([$results,
        'message' => 'All Taeames']);

       }
    }
    public function getMemberOfTeam(Department $department)

    {
        $members=$department->users()->get();
        return ResponseHelper::success($members);

    }
}
