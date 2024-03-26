<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Jobs\StoreAttendanceLogsJob;
use App\Models\Absences;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Contract;
use App\Models\Date;
use App\Models\Decision;
use App\Models\Late;
use App\Models\Policy;
use App\Models\User;
use App\Models\UserInfo;
use App\Services\FingerprintService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TADPHP\TADFactory;


require 'tad\vendor\autoload.php';

class AttendanceController extends Controller
{
    public $fingerprintService;

    public function __construct(FingerprintService $fingerprintService)
    {
        $this->fingerprintService = $fingerprintService;

    }

    public function getAttendanceLogs()
    {
        $tad_factory = new TADFactory(['ip' => '192.168.2.202']);
        $tad = $tad_factory->get_instance();
        $logs = $tad->get_att_log();
        $xml = simplexml_load_string($logs);
        $array = json_decode(json_encode($xml));
        return ResponseHelper::success($array, null, 'all logs returned successfully', 200);
    }

    public function employees_percent()
    {
        $all_users = User::query()->count();
        $attended_users = Attendance::whereDate('datetime', now()->format('Y-m-d'))->where('status', '0')->count();
        return ResponseHelper::success(
            [
                'present_employees' => $attended_users,
                'total_employees' => $all_users
            ],
            null,
            'attended users returned successfully',
            200
        );
    }

    public function storeAttendanceLogs(Request $request)
    {
        return DB::transaction(function () use ($request) {
            //Storing attendance
            $branch = Branch::findOrFail($request->branch_id);
            $tad_factory = new TADFactory(['ip' => $branch->fingerprint_scanner_ip]);
            $tad = $tad_factory->get_instance();
            $all_user_info = $tad->get_all_user_info();
            $dt = $tad->get_date();
            $logs = $tad->get_att_log();
            $xml = simplexml_load_string($logs);
            $array = json_decode(json_encode($xml), true);
            $logsData = $array['Row'];
            $uniqueDates = [];

            foreach ($logsData as $log) {
                $this->fingerprintService->storeAttendance($log);
                $date = date('Y-m-d', strtotime($log['DateTime']));
                Date::updateOrCreate(['date' => $date]);
                $checkInDate = substr($log['DateTime'], 0, 10);
                if (!in_array($checkInDate, $uniqueDates)) {
                    $uniqueDates[] = $checkInDate;
                }
            }
            //Storing delays
            $allAttendances = Attendance::query()
                //->where('status', '0')
                ->get();
            foreach ($allAttendances as $attendance) {

                $this->fingerprintService->storeUserDelays($attendance->pin, $request->branch_id, $attendance->datetime);

            }
            //Storing absence
            foreach ($uniqueDates as $date) {
                $this->fingerprintService->storeUserAbsences($date, $request->branch_id);
            }
            return ResponseHelper::success([], null, 'attendances logs stored successfully', 200);
        });



    }

    public function showAttendanceLogs()
    {
        $result = User::with('department')->with('attendance')->get()->toArray();
        return ResponseHelper::success(
            $result
        );
    }

    public function DayAttendance($date)
    {
        $users = User::with('department', 'userInfo')
            ->with(['attendance' => function ($query) use ($date) {
                $query->whereDate('datetime', $date);
            }])
            ->has('attendance')
            ->get();
        return ResponseHelper::success($users);
    }

    public function showAttendanceUser($user)
    {
        $result = User::with('attendance')
            ->where('id', $user)
            ->get()->toArray();
        return ResponseHelper::success($result);
    }
}
