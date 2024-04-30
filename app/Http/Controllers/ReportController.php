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
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function store(StoreReportRequest $request) //TODO delete method or not
    {
        return $this->ReportServices->StoreReport($request);
    }

    /**
     * Remove existing report by a specific user.
     * [ReportServices => RemoveReport]
     * @param Report
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function remove($id) //TODO delete method or not
    {
        return $this->ReportServices->RemoveReport($id);
    }

    /**
     * Get reports of the authenticated user.
     * [ReportServices => MyReports]
     * @param none
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function my_reports() //TODO delete method or not
    {
        return $this->ReportServices->MyReports();
    }

    /**
     * Get all reports of all users in a specific branch.
     * [ReportServices => AllReports]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function all_reports(Request $request) //TODO delete method or not
    {
        return $this->ReportServices->AllReports($request);
    }

    /**
     * Get all reports of all users in a specific branch at the current day.
     * [ReportServices => DailyReports]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function daily_reports(Request $request) //TODO delete method or not
    {
        return $this->ReportServices->DailyReports($request);
    }

    /**
     * Get CHECK-INs & CHECK-OUTs of a user in a specific day.
     * [ReportServices => UserChecks]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function user_checks(Request $request)
    {
        return $this->ReportServices->UserChecks($request);
    }

    /**
     * Get the report of a user in a specific date.
     * [ReportServices => Report]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function report(Request $request)
    {
        return $this->ReportServices->Report($request);
    }

    /**
     * Get the rates of a user in a given date.
     * [ReportServices => RatesByDate]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function ratesByDate(Request $request)
    {
        return $this->ReportServices->RatesByDate($request);
    }

}
