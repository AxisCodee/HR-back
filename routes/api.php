<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ContractController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});
Route::get('getAttendanceLogs', [AttendanceController::class, 'getAttendanceLogs']);
Route::get('storeAttendanceLogs', [AttendanceController::class, 'storeAttendanceLogs']);
Route::get('showAttendanceLogs', [AttendanceController::class, 'showAttendanceLogs']);

Route::prefix('contract')->group(function(){
Route::controller(ContractController::class)->group(function () {
    Route::post('Add', 'store');
    Route::get('Show/{id}', 'show');
    Route::get('All', 'index');
    Route::delete('Delete/{contract}', 'destroy');
});
});



