<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ReportController;

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
        Route::get('Show/{contract}', 'show');
        Route::get('All', 'index');
        Route::delete('Delete/{contract}', 'destroy');
    });
});

Route::prefix('Report')->group(function(){
    Route::controller(ReportController::class)->group(function(){
        Route::post('Add','store');
        Route::get('my_reports','all_reports');
        Route::delete('remove/{report}','remove');
    });
});

