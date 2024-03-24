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
        try {
            $newreport = $this->ReportServices->StoreReport($request);
            return $newreport;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Remove existing report by a specific user.
     * [ReportServices => RemoveReport]
     * @param Report
     * @return ResponseHelper
     */
    public function remove($id) //TODO delete method or not
    {
        try {
            $remove = $this->ReportServices->RemoveReport($id);
            return $remove;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Get reports of the authenticated user.
     * [ReportServices => MyReports]
     * @param none
     * @return ResponseHelper
     */
    public function my_reports() //TODO delete method or not
    {
        try {
            $reports = $this->ReportServices->MyReports();
            return $reports;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Get all reports of all users in a specific branch.
     * [ReportServices => AllReports]
     * @param Request
     * @return ResponseHelper
     */
    public function all_reports(Request $request) //TODO delete method or not
    {
        try {
            $allreports = $this->ReportServices->AllReports($request);
            return $allreports;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Get all reports of all users in a specific branch at the current day.
     * [ReportServices => DailyReports]
     * @param Request
     * @return ResponseHelper
     */
    public function daily_reports(Request $request) //TODO delete method or not
    {
        try {
            $todayreports = $this->ReportServices->DailyReports($request);
            return $todayreports;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Get CHECK-INs & CHECK-OUTs of a user in a specific day.
     * [ReportServices => UserChecks]
     * @param Request
     * @return ResponseHelper
     */
    public function user_checks(Request $request)
    {
        try {
            $checks = $this->ReportServices->UserChecks($request);
            return $checks;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Get the report of a user in a specific date.
     * [ReportServices => Report]
     * @param Request
     * @return ResponseHelper
     */
    public function report(Request $request)
    {
        try {
            $report = $this->ReportServices->Report($request);
            return $report;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Get the rates of a user in a given date.
     * [ReportServices => RatesByDate]
     * @param Request
     * @return ResponseHelper
     */
    public function ratesByDate(Request $request)
    {
        try {
            $rates = $this->ReportServices->RatesByDate($request);
            return $rates;
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    public function checksPercentage(Request $request)
    {
        $user = User::query()->findOrFail($request->user_id);
        $date = $request->date;
        $status = $request->status;
        if (strlen($date) == 4) {
            $format = 'Y';
        } else {
            $format = 'Y-m';
        }
        $result = $this->ReportServices->getUserChecksPercentage($user, $date, $format, $status);
        return ResponseHelper::success($result);
    }
}
