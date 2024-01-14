<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use TADPHP\TADFactory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

require 'tad/vendor/autoload.php';

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {  
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);
        if (!$token) {
            return ResponseHelper::error('phonenumber or password are not correct', null, 'error', 401);
        }
        $user = Auth::user();
        return ResponseHelper::success([
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {

        $tad_factory = new TADFactory(['ip'=>'192.168.2.202']);
        $tad = $tad_factory->get_instance();
        // $r = $tad->set_user_info([
        //     'pin' => $request->id,//this is the pin2 in the returned response
        //     'name'=> $request->first_name,
        //     'privilege'=> 0,//if you want to add a superadmin user make the privilege as '14'.
        //     'password' => $request->password]);
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role'=>$request->role,
            'department_id' => $request->department_id,
            'password' => Hash::make($request->password),
            'pin' => $request->pin,//this is the pin2 in the returned response


        ]);
        $user->assignRole($request->role);


        return ResponseHelper::success([
            'message' => 'User created successfully',
            'user' => $user,
        ]);

    }

    public function logout()
    {
        Auth::logout();
        return  ResponseHelper::success([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
