<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::middleware('auth:api')->group(function(){
    Route::get('getAttendanceLogs', [AttendanceController::class, 'getAttendanceLogs']);
    //all users
    Route::get('getallusers',[UserController::class,'all_users']);
});
