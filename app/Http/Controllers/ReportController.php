<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Services\ReportServices;
use App\Http\Requests\ReportRequest\StoreReportRequest;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected $ReportServices;

    /**
     * Define the constructor to use the service.
     * @param ReportServices
     * @return none
     */
    public function __construct(ReportServices $ReportServices)
    {
        $this->ReportServices = $ReportServices;
    }

    /**
     * Validate request & Store the new report.
     * [ReportServices => StoreReport]
     * @param StoreReportRequest
     * @return ResponseHelper
     */
    public function store(StoreReportRequest $request) //TODO delete method or not
    {
            $newreport = $this->ReportServices->StoreReport($request);
            return $newreport;

    }

    /**
     * Remove existing report by a specific user.
     * [ReportServices => RemoveReport]
     * @param Report
     * @return ResponseHelper
     */
    public function remove($id) //TODO delete method or not
    {
            $remove = $this->ReportServices->RemoveReport($id);
            return $remove;

    }

    /**
     * Get reports of the authenticated user.
     * [ReportServices => MyReports]
     * @param none
     * @return ResponseHelper
     */
    public function my_reports() //TODO delete method or not
    {
            $reports = $this->ReportServices->MyReports();
            return $reports;

    }

    /**
     * Get all reports of all users in a specific branch.
     * [ReportServices => AllReports]
     * @param Request
     * @return ResponseHelper
     */
    public function all_reports(Request $request) //TODO delete method or not
    {
            $allreports = $this->ReportServices->AllReports($request);
            return $allreports;

    }

    /**
     * Get all reports of all users in a specific branch at the current day.
     * [ReportServices => DailyReports]
     * @param Request
     * @return ResponseHelper
     */
    public function daily_reports(Request $request) //TODO delete method or not
    {
            $todayreports = $this->ReportServices->DailyReports($request);
            return $todayreports;

    }

    /**
     * Get CHECK-INs & CHECK-OUTs of a user in a specific day.
     * [ReportServices => UserChecks]
     * @param Request
     * @return ResponseHelper
     */
    public function user_checks(Request $request)
    {
            $checks = $this->ReportServices->UserChecks($request);
            return $checks;

    }

    /**
     * Get the report of a user in a specific date.
     * [ReportServices => Report]
     * @param Request
     * @return ResponseHelper
     */
    public function report(Request $request)
    {
            return $this->ReportServices->Report($request);

    }

    /**
     * Get the rates of a user in a given date.
     * [ReportServices => RatesByDate]
     * @param Request
     * @return ResponseHelper
     */
    public function ratesByDate(Request $request)
    {
            $rates = $this->ReportServices->RatesByDate($request);
            return $rates;
       
    }

}
