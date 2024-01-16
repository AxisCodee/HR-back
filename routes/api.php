<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\api\GmailController;
use App\Http\Controllers\RequestController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});
Route::get('getAttendanceLogs', [AttendanceController::class, 'getAttendanceLogs']);
Route::get('storeAttendanceLogs', [AttendanceController::class, 'storeAttendanceLogs']);
Route::get('showAttendanceLogs', [AttendanceController::class, 'showAttendanceLogs']);
Route::get('showPercent', [AttendanceController::class, 'employees_percent']);

Route::prefix('contract')->group(function () {

    Route::controller(ContractController::class)->group(function () {
        Route::post('Add', 'store');
        Route::get('Show/{id}', 'show');
        Route::get('All', 'index');
        Route::delete('Delete/{contract}', 'destroy');
    });
});

Route::prefix('Report')->group(function () {
    Route::controller(ReportController::class)->group(function () {
        Route::post('Add', 'store');
        Route::get('daily', 'daily_reports');
        Route::get('myReports', 'all_reports');
        Route::delete('remove/{report}', 'remove');
    });
});
Route::prefix('Users')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('allUser', 'all_users');
    });
});

Route::prefix('Decision')->group(function () {
    Route::controller(DecisionController::class)->group(function () {
        Route::post('Add', 'new_decision');
        Route::delete('remove/{decision}', 'remove_decision');
        Route::post('edit/{decision}', 'edit_decision');
        Route::get('all', 'all_decisions');
        Route::get('my_decisions', 'my_decisions');
    });
});

Route::prefix('Calendar')->group(function () {
    Route::controller(CalendarController::class)->group(function () {
        Route::post('Add', 'add_event');
        Route::delete('Remove/{event}', 'cancel_event');
        Route::get('All', 'all_events');
        Route::post('Edit/{event}', 'update_event');
        Route::get('EventsByDay', 'day_events');
        Route::get('EventsByWeek', 'week_events');
        Route::get('EventsByMonth', 'month_events');
    });
});

Route::prefix('Gmail')->group(function () {
    Route::controller(GmailController::class)->group(function () {
        Route::get('google/login/url', 'getAuthUrl');
        Route::post('google/auth/login', 'postLogin');
        Route::post('google/getUserInfo', 'getUserInfo');
        Route::post('google/mailbox', 'mail');
        Route::post('google/sendEmail', 'sendEmail');
        Route::post('google/search', 'search');
        Route::post('google/getMessageById', 'getMessageById');
        Route::post('google/deleteMessages', 'deleteMessages');
        Route::post('google/starMessages', 'starMessages');
    });
});
Route::prefix('contract')->group(function () {
    Route::controller(ContractController::class)->group(function () {
        Route::post('Add', 'store');
        Route::get('Show/{id}', 'show');
        Route::get('All', 'index');
        Route::delete('Delete/{contract}', 'destroy');
    });
});

Route::prefix('Report')->group(function () {
    Route::controller(ReportController::class)->group(function () {
        Route::post('Add', 'store');
        Route::get('daily', 'daily_reports');
        Route::get('myReports', 'all_reports');
        Route::delete('remove/{report}', 'remove');
        Route::post('InsnOuts', 'user_checks');
    });
});
Route::prefix('Users')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('allUser', 'all_users');

    });
});

Route::prefix('Decision')->group(function () {
    Route::controller(DecisionController::class)->group(function () {
        Route::post('Add', 'new_decision');
        Route::delete('remove/{decision}', 'remove_decision');
        Route::post('edit/{decision}', 'edit_decision');
        Route::get('all', 'all_decisions');
        Route::get('my_decisions', 'my_decisions');
    });
});

Route::prefix('Calendar')->group(function () {
    Route::controller(CalendarController::class)->group(function () {
        Route::post('Add', 'add_event');
        Route::delete('Remove/{event}', 'cancel_event');
        Route::get('All', 'all_events');
        Route::post('Edit/{event}', 'update_event');
        Route::get('EventsByDay', 'day_events');
        Route::get('EventsByWeek', 'week_events');
        Route::get('EventsByMonth', 'month_events');
        Route::get('/EventDate/{date}', 'getEvenetsByDay');

    });
});

Route::prefix('Request')->group(function () {
    Route::controller(RequestController::class)->group(function () {
        Route::post('All', 'index');
        Route::post('Add', 'store');
        Route::post('Edit', 'update');
        Route::delete('Delete', 'destroy');
        Route::post('Accept', 'acceptRequest');
        Route::post('Reject', 'rejectRequest');


    });


Route::prefix('Team')->group(function(){
    Route::controller(UserController::class)->group(function(){
        Route::get('getTeams','getTeams');
        Route::post('storeTeams','storeTeams');
        Route::post('updateTeam/{team}','updateTeams');
        Route::delete('deleteTeam/{team}','deleteTeam');
    });
});


Route::prefix('Request')->group(function(){
    Route::controller(RequestController::class)->group(function(){
        Route::get('All','index');
        Route::post('Add','store');
        Route::post('Update/{id}','update');
        Route::post('accepteRequest/{request}','accepteRequest');
        Route::post('rejectRequest/{request}','rejectRequest');
        Route::delete('Delete/{request}','destory');

});
});


});

