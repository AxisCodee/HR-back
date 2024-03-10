<?php

use App\Http\Controllers\AbsencesController;
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

Route::middleware('exceptionHandler')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
    });

//All the encapsulated APIs for the admin
    Route::middleware('auth', 'admin')->group(function () {

        Route::controller(AuthController::class)->group(function () {
            Route::post('register', 'register');
        });

        Route::controller(AttendanceController::class)->group(function () {
            Route::get('getAttendanceLogs', [AttendanceController::class, 'getAttendanceLogs']);
            Route::get('showAttendanceLogs', [AttendanceController::class, 'showAttendanceLogs']);
            Route::get('showPercent', [AttendanceController::class, 'employees_percent']);
            Route::get('DayAttendance/{date}', [AttendanceController::class, 'DayAttendance']);
            Route::get('showAttendanceUser/{user}', [AttendanceController::class, 'showAttendanceUser']);
        });

    Route::prefix('contract')->group(function () {
        Route::controller(ContractController::class)->group(function () {
            Route::post('Add', 'store');
            Route::get('Show/{id}', 'show');
            Route::get('All', 'index');
            Route::delete('Delete/{contract}', 'destroy');
            Route::post('Update/{contract}','update');
        });
    });

        Route::prefix('Users')->group(function () {
            Route::controller(UserController::class)->group(function () {
                Route::get('exceptAdmin', 'not_admin');
                Route::get('allUser', 'all_users');
                Route::delete('removeUser/{user}', 'remove_user');
                Route::post('EditUser/{user}', 'edit_user');
                Route::get('Deps&Roles', 'all_dep_rul');
                Route::get('MembersHierarchy', 'roleHierarchy');
                Route::get('user/{id}', 'specific_user');
                Route::get('professional', 'user_prof');
                Route::get('resignedusers', 'resignedusers');
                Route::post('updateUser/{user}', 'updateUser');

            });
        });

        Route::prefix('Decision')->group(function () {
            Route::controller(DecisionController::class)->group(function () {
                Route::post('Add', 'new_decision');
                Route::delete('remove/{decision}', 'remove_decision');
                Route::post('edit/{decision}', 'edit_decision');
                Route::get('all', 'all_decisions');
                Route::get('getUserDecisions', 'getUserDecisions');
                Route::get('getUserAbsence', 'getUserAbsence');
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

        Route::prefix('Report')->group(function () {
            Route::controller(ReportController::class)->group(function () {
                Route::get('daily', 'daily_reports');
                Route::get('All', 'all_reports');
                Route::delete('remove/{report}', 'remove');
                Route::post('InsnOuts', 'user_checks');
                Route::post('reportByDay', 'report');

                Route::post('ratesByDate', 'ratesByDate');
            });
        });

        Route::prefix('Request')->group(function () {
            Route::controller(RequestController::class)->group(function () {
                Route::get('All', 'index');
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
                Route::post('storeTeams', 'addTeams');
                Route::post('AddMembers/{team}', 'Addmembers');
                // Route::post('updateTeam/{team}', 'updateTeams');
                Route::delete('deleteTeam/{team}', 'deleteTeam');
                Route::post('RemoveMember/{user}', 'removeFromTeam');
                Route::post('addTeams', 'addTeams');
                Route::post('updateTeam/{id}', 'updateTeam');


            });
        });

        Route::prefix('Deposit')->group(function () {
            Route::controller(DepositController::class)->group(function () {
                Route::get('All', 'index');
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

        Route::prefix('Language')->group(function () {
            Route::controller(LanguageController::class)->group(function () {
                Route::post('Add', 'store');
                Route::post('Update/{id}', 'update');
                Route::delete('Delete/{id}', 'destroy');
            });
        });

        Route::prefix('Notes')->group(function () {
            Route::controller(NoteController::class)->group(function () {
                Route::post('Add', 'store');
                Route::post('Update/{id}', 'update');
                Route::delete('Delete/{id}', 'destroy');
                Route::get('userNote/{id}', 'user_notes');
            });
        });

        Route::prefix('Certificate')->group(function () {
            Route::controller(CertificateController::class)->group(function () {
                Route::post('store', 'store');
                Route::post('update/{id}', 'update');
                Route::delete('destroy/{id}', 'destroy');
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
                Route::get('All', 'index');
                Route::get('Show/{user}', 'show');
                Route::post('update', 'update');
                Route::get('Uabsences', 'unjustifiedAbsence');
                Route::post('DynamicDecision/{absences}', 'DynamicDecision');
                Route::post('AddAbsence', 'store_absence');
                Route::get('getAbsences/{user}', 'getAbsences');
                Route::delete('deleteAbsence/{absence}', 'deleteAbsence');
                Route::post('store_one_absence', 'storeAbsence'); //store one absence
            });
        });

        Route::prefix('EmployeeOfMonth')->group(function () {
            Route::controller(EmpOfMonthController::class)->group(function () {
                Route::get('All', 'index');
                Route::post('Add', 'store');
                Route::get('Show', 'show');
            });
        });

        Route::prefix('Policy')->group(function () {
            Route::controller(PolicyController::class)->group(function () {
                Route::get('Show', 'show');
                Route::post('Add', 'store');
                Route::post('Update', 'update');
                Route::delete('Delete', 'destroy');
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
                Route::get('showLate', 'showLate');
                Route::post('rejectAlert', 'rejectAlert');
                Route::post('acceptAlert', 'acceptAlert');
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
    });


//All APIs for the normal user (not encapsulated)
    Route::prefix('Decision')->group(function () {
        Route::controller(DecisionController::class)->group(function () {
<<<<<<< HEAD
            Route::post('Add', 'new_decision');
            Route::delete('remove/{decision}', 'remove_decision');
            Route::post('edit/{decision}', 'edit_decision');
            Route::get('all', 'all_decisions');
            Route::get('getUserDecisions', 'getUserDecisions');
            Route::get('getUserAbsence', 'getUserAbsence');
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

    Route::prefix('Report')->group(function () {
        Route::controller(ReportController::class)->group(function () {
            Route::get('daily', 'daily_reports');
            Route::get('All', 'all_reports');
            Route::delete('remove/{report}', 'remove');
            Route::post('InsnOuts', 'user_checks');
            Route::post('reportByDay', 'report');

            Route::post('ratesByDate', 'ratesByDate');
        });
    });

Route::prefix('Request')->group(function () {
    Route::controller(RequestController::class)->group(function () {
        Route::get('All', 'index');
        Route::get('Me', 'show');
        Route::get('Complaints', 'getComplaints');
        Route::get('info/{id}', 'getRequest');
        Route::post('Add', 'store');
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
        Route::post('storeTeams', 'addTeams');
        Route::post('AddMembers/{team}', 'Addmembers');
       // Route::post('updateTeam/{team}', 'updateTeams');
        Route::delete('deleteTeam/{team}', 'deleteTeam');
        Route::post('RemoveMember/{user}', 'removeFromTeam');
        Route::post('addTeams', 'addTeams');
        Route::post('updateTeam/{id}', 'updateTeam');



    });
});

    Route::prefix('Deposit')->group(function () {
        Route::controller(DepositController::class)->group(function () {
            Route::get('All', 'index');
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

    Route::prefix('Language')->group(function () {
        Route::controller(LanguageController::class)->group(function () {
            Route::post('Add', 'store');
            Route::post('Update/{id}', 'update');
            Route::delete('Delete/{id}', 'destroy');
        });
    });

    Route::prefix('Notes')->group(function () {
        Route::controller(NoteController::class)->group(function () {
            Route::post('Add', 'store');
            Route::post('Update/{id}', 'update');
            Route::delete('Delete/{id}', 'destroy');
            Route::get('userNote/{id}', 'user_notes');
        });
    });

    Route::prefix('Certificate')->group(function () {
        Route::controller(CertificateController::class)->group(function () {
            Route::post('store', 'store');
            Route::post('update/{id}', 'update');
            Route::delete('destroy/{id}', 'destroy');
=======
            Route::get('my_decisions', 'my_decisions');
>>>>>>> 7b7527961e52ee30578f6298d22670f72e8a675e
        });
    });

    Route::prefix('UserInfo')->group(function () {
        Route::controller(UserInfoController::class)->group(function () {
            Route::get('Show/{id}', 'show');
        });
    });

    Route::prefix('Notes')->group(function () {
        Route::controller(NoteController::class)->group(function () {
            Route::get('All', 'index');
<<<<<<< HEAD
            Route::get('Show/{user}', 'show');
            Route::post('update', 'update');
            Route::get('Uabsences', 'unjustifiedAbsence');
            Route::post('DynamicDecision/{absences}', 'DynamicDecision');
            Route::post('AddAbsence', 'store_absence');
            Route::get('getAbsences/{user}', 'getAbsences');
            Route::get('currentAbsence', 'cuurentAbsence');
            Route::delete('deleteAbsence/{absence}', 'deleteAbsence');
            Route::post('store_one_absence', 'storeAbsence'); //store one absence
=======
>>>>>>> 7b7527961e52ee30578f6298d22670f72e8a675e
        });
    });

    Route::prefix('Report')->group(function () {
        Route::controller(ReportController::class)->group(function () {
            Route::get('myReports', 'my_reports');
        });
    });

    Route::prefix('Request')->group(function () {
        Route::controller(RequestController::class)->group(function () {
            Route::post('Add', 'store');
            Route::get('Me', 'show');
        });
    });


    Route::get('storeAttendanceLogs', [AttendanceController::class, 'storeAttendanceLogs']);
});
