<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
<<<<<<< HEAD
use App\Http\Controllers\ContractController;
=======
use App\Http\Controllers\UserController;
use App\Http\Controllers\DecisionController;
>>>>>>> 243277caccb1e07700f66550efa107b9de1ca8b7

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});
Route::get('getAttendanceLogs', [AttendanceController::class, 'getAttendanceLogs']);
Route::get('storeAttendanceLogs', [AttendanceController::class, 'storeAttendanceLogs']);
Route::get('showAttendanceLogs', [AttendanceController::class, 'showAttendanceLogs']);
<<<<<<< HEAD

Route::prefix('contract')->group(function(){
Route::controller(ContractController::class)->group(function () {
    Route::post('Add', 'store');
    Route::get('Show/{contract}', 'show');
    Route::get('All', 'index');
    Route::delete('Delete/{contract}', 'destroy');
});
});



=======
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
>>>>>>> 243277caccb1e07700f66550efa107b9de1ca8b7
