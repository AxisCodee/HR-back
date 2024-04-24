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
        $tad_factory = new TADFactory(['ip' => '192.168.2.201']);
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
       $job= dispatch(new StoreAttendanceLogsJob($request->branch_id, $this->fingerprintService));
        
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
