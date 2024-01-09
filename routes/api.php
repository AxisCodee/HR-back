<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DecisionController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});
Route::get('getAttendanceLogs', [AttendanceController::class, 'getAttendanceLogs']);
Route::get('storeAttendanceLogs', [AttendanceController::class, 'storeAttendanceLogs']);
Route::get('showAttendanceLogs', [AttendanceController::class, 'showAttendanceLogs']);
//get all attended users
Route::get('percent', [AttendanceController::class, 'employees_percent']);
//create new decision
Route::post('new_decision',[DecisionController::class,'new_decision']);
//remove existing decision
Route::delete('remove_decision/{decision}',[DecisionController::class,'remove_decision']);
//edit existing decision
Route::post('edit_decision/{decision}',[DecisionController::class,'edit_decision']);
//get all decision
Route::get('all_decision',[DecisionController::class,'all_decisions']);
