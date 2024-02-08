<?php

use App\Http\Controllers\AbsencesController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\api\GmailController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\EmpOfMonthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\RateTypeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\StudySituationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LateController;
use App\Http\Controllers\UserInfoController;
use Illuminate\Support\Facades\Route;

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
Route::get('DayAttendance/{date}', [AttendanceController::class, 'DayAttendance']);

Route::get('showAttendanceUser/{user}', [AttendanceController::class, 'showAttendanceUser']);

Route::prefix('contract')->group(function () {
    Route::controller(ContractController::class)->group(function () {
        Route::post('Add', 'store');
        Route::get('Show/{id}', 'show');
        Route::get('All', 'index');
        Route::delete('Delete/{contract}', 'destroy');
    });
});

Route::prefix('Users')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('allUser', 'all_users');
        Route::delete('removeUser/{user}', 'remove_user');
        Route::post('EditUser/{user}', 'edit_user');
        Route::get('Deps&Roles', 'all_dep_rul');
        Route::get('MembersHierarchy', 'roleHierarchy');
        Route::get('user/{id}', 'specific_user');
        Route::get('professional', 'user_prof');
    });
});

Route::prefix('Decision')->group(function () {
    Route::controller(DecisionController::class)->group(function () {
        Route::post('Add', 'new_decision');
        Route::delete('remove/{decision}', 'remove_decision');
        Route::post('edit/{decision}', 'edit_decision');
        Route::get('all/{branchId}', 'all_decisions');
        Route::get('my_decisions', 'my_decisions');
        Route::get('user_desicions/{id}', 'user_decisions');
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

Route::prefix('Report')->group(function () {
    Route::controller(ReportController::class)->group(function () {
        Route::post('Add', 'store');
        Route::get('daily/{branchId}', 'daily_reports');
        Route::get('myReports', 'my_reports');
        Route::get('All', 'all_reports');
        Route::delete('remove/{report}', 'remove');
        Route::post('InsnOuts', 'user_checks');

        //
        Route::post('reportByDay', 'report');
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
        Route::get('All/{branchId}', 'index');
        Route::get('Me', 'show');
        Route::get('Complaints', 'getComplaints');
        Route::get('info/{id}', 'getRequest');
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::post('accepteRequest/{request}', 'acceptRequest');
        Route::post('rejectRequest/{request}', 'rejectRequest');
        Route::delete('Delete/{request}', 'destroy');
    });
});
Route::prefix('Team')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('getTeams', 'getTeams');
        Route::post('storeTeams', 'storeTeams');
        Route::post('AddMembers/{team}', 'Addmembers');
        Route::post('updateTeam/{team}', 'updateTeams');
        Route::delete('deleteTeam/{team}', 'deleteTeam');
        Route::post('RemoveMember/{user}', 'remove_from_team');
    });
});

///thales
Route::prefix('Address')->group(function () {
    Route::controller(AddressController::class)->group(function () {
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        //Route::get('Show/{id}', 'show');
        Route::delete('Delete/{id}', 'destory');
    });
});
Route::prefix('Deposit')->group(function () {
    Route::controller(DepositController::class)->group(function () {
        Route::get('All/{branchId}', 'index');
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::get('Show', 'show');
        Route::delete('Delete/{id}', 'destroy');
    });
});
Route::prefix('Career')->group(function () {
    Route::controller(CareerController::class)->group(function () {
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::delete('Delete/{id}', 'destroy');
    });
});

Route::prefix('StudySituations')->group(function () {
    Route::controller(StudySituationController::class)->group(function () {
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::delete('Delete/{id}', 'destroy');
    });
});
Route::prefix('Certificates')->group(function () {
    Route::controller(CertificateController::class)->group(function () {
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::delete('Delete/{id}', 'destroy');
    });
});

Route::prefix('Language')->group(function () {
    Route::controller(LanguageController::class)->group(function () {
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::delete('Delete/{id}', 'destroy');
    });
});
Route::prefix('Notes')->group(function () {
    Route::controller(NoteController::class)->group(function () {
        Route::get('All', 'index');
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::delete('Delete/{id}', 'destroy');
        Route::get('userNote/{id}', 'user_notes');
    });
});

Route::prefix('UserInfo')->group(function () {
    Route::controller(UserInfoController::class)->group(function () {
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::get('Show/{id}', 'show');
        Route::post('updateSalary/{id}', 'updateSalary');
    });
});

Route::prefix('Absence')->group(function () {
    Route::controller(AbsencesController::class)->group(function () {
        Route::get('All/{branch}', 'index');
        Route::get('Show/{user}', 'show');
        Route::get('Uabsences', 'unjustifiedAbsence');
        Route::post('DynamicDecision/{absences}', 'DynamicDecision');
        Route::post('AddAbsence', 'store_absence');
    });
});

Route::prefix('EmployeeOfMonth')->group(function () {
    Route::controller(EmpOfMonthController::class)->group(function () {
        Route::get('All/{branchId}', 'index');
        Route::post('Add', 'store');
        Route::get('Show/{branchId}', 'show');
    });
});

Route::prefix('Policy')->group(function () {
    Route::controller(PolicyController::class)->group(function () {
        Route::get('Show/{id}', 'show');
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
    });
});
Route::prefix('branch')->group(function () {
    Route::controller(BranchController::class)->group(function () {
        Route::get('All', 'index');
        Route::get('Show/{id}', 'show');
        Route::post('Add', 'store');
        Route::post('Update/{id}', 'update');
        Route::post('Delete/{id}', 'destroy');
    });
});
Route::prefix('Rate')->group(function () {
    Route::controller(RateController::class)
        ->group(function () {
            Route::post('setRate', 'setRate');
            Route::get('getRate/{id}', 'getRate');
            Route::get('allRates', 'allRates');
            Route::get('userRates/{date}', 'userRates');
        });
    Route::controller(RateTypeController::class)->group(function () {

        Route::get('getRateType/{id}', 'getRateType');

        Route::get('BranchTypes/{id}', 'show'); //show types for branch
        //Route::get('ShowType/{id}', 'show');//
        Route::post('AddType', 'store'); //
        Route::post('UpdateType/{id}', 'update');
    });
});
Route::prefix('Late')->group(function () {
    Route::controller(LateController::class)->group(function () {
        Route::get('Lates', ' unjustifiedLate');
        Route::post('makeDecision/{lates}', 'makeDecision');
        Route::post('dynamicDecision', 'dynamicDecision');
    });
});
