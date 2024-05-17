<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Date;
use App\Models\User;
use App\Services\FingerprintService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use TADPHP\TADFactory;


require 'tad\vendor\autoload.php';

class AttendanceController extends Controller
{
    public $fingerprintService;

    public function __construct(FingerprintService $fingerprintService)
    {
        $this->fingerprintService = $fingerprintService;
    }

    public function getAttendanceLogs(Request $request)//TODO
    {
        $fingerprintIP = Branch::query()->findOrFail($request->branch_id)->fingerprint_scanner_ip;
        $tad_factory = new TADFactory(['ip' => $fingerprintIP]);
        $tad = $tad_factory->get_instance();
        $logs = $tad->get_att_log();
        $xml = simplexml_load_string($logs);
        $array = json_decode(json_encode($xml));
        return ResponseHelper::success($array, null, 'all logs returned successfully', 200);
    }

    public function employees_percent(Request $request)
{
    $users = User::query()->where('branch_id', $request->branch_id)
        ->wherewhere('role', '!=', 'admin');

    $all_users = $users->count();

    $userspin = $users->pluck('pin')->toArray();

    $attended = Attendance::query();
    $attended_users= $attended ->whereIn('pin', $userspin)
        ->where('branch_id', $request->branch_id)
        ->whereDate('datetime','>=', now()->format('Y-m-d H:i:s'))
        ->where('status', '0')
        ->count();

    return ResponseHelper::success([
        'present_employees' => $attended_users,
        'total_employees' => $all_users
    ], null, 'Attended users returned successfully');
}

    public function storeAttendanceLogs(Request $request)
    {
        //$job = dispatch(new StoreAttendanceLogsJob($request->branch_id, $this->fingerprintService));
        return DB::transaction(function () use ($request) {
            //Storing attendance
            $branchId = $request->branch_id;
            $branch = Branch::findOrFail($branchId);
            if ($branch->users->count() == 0) {
                return ResponseHelper::error('This branch does not have employees.');
            }
            $tad_factory = new TADFactory(['ip' => $branch->fingerprint_scanner_ip]);
            $tad = $tad_factory->get_instance();
            // $all_user_info = $tad->get_all_user_info();
            // $dt = $tad->get_date();
            $logs = $tad->get_att_log();
            //check date table and store attendance
            $uniqueDates = [];
            //dd(Date::query()->where('branch_id', $branchId)->get()->count() > 0);
            if (Date::query()->where('branch_id', $branchId)->get()->count() > 0) {
                $start = Date::latest('date')->value('date');
                $end = Carbon::now()->format('Y-m-d');
                $filtered_att_logs = $logs->filter_by_date(
                    ['start' => $start, 'end' => $end]
                );
                $xml = simplexml_load_string($filtered_att_logs);
                $uniqueDates = $this->fingerprintService->convertAndStoreAttendance($xml, $branchId);
                $allAttendances = Attendance::query()
                    ->where('branch_id', $branchId)
                    ->whereRaw('DATE(datetime) BETWEEN ? AND ?', [$start, $end])
                    ->get();
            }
            if (Date::query()->where('branch_id', $branchId)->get()->count() == 0) {
                $xml = simplexml_load_string($logs);
                $uniqueDates = $this->fingerprintService->convertAndStoreAttendance($xml, $branchId);
                $allAttendances = Attendance::query()
                    ->where('branch_id', $branchId)
                    ->get();
            }
            //Storing delays
            foreach ($allAttendances as $attendance) {
                $this->fingerprintService->storeUserDelays($attendance->pin, $branchId, $attendance->datetime, '0');
                $this->fingerprintService->storeUserDelays($attendance->pin, $branchId, $attendance->datetime, '1');
            }
            //Storing absence
            foreach ($uniqueDates as $date) { //replacing the delay with absence
                $this->fingerprintService->clearDelays($branchId, $date); //delete the delay
                $this->fingerprintService->storeUserAbsences($date, $branchId); //store absence
            }
            return ResponseHelper::success([], null, 'Attendances logs stored successfully');
        });
    }

    public function importFromFingerprint(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $fingerprintIP = Branch::query()->findOrFail($request->branch_id)->fingerprint_scanner_ip;
            $tad_factory = new TADFactory(['ip' => $fingerprintIP]);
            $tad = $tad_factory->get_instance();
            $all_user_info = $tad->get_all_user_info();
            $xml = simplexml_load_string($all_user_info);
            if ($xml === false) {
                error_log('Failed to parse XML string.');
                foreach (libxml_get_errors() as $error) {
                    error_log($error->message);
                }
            } else {
                $array = json_decode(json_encode($xml), true);
                foreach ($array['Row'] as $row) {
                    $user = new User();
                    $user->pin = intval($row['PIN2']);
                    $user->first_name = !empty($row['Name']) ? $row['Name'] : "name";
                    $user->last_name = "null";
                    $user->email = intval($row['PIN2']) . "@gmail.com";
                    $user->password = Hash::make('password');
                    $user->specialization = "specialization";
                    $user->branch_id = $request->branch_id;
                    // Set other user properties...
                    $user->save();
                }
            }
            return ResponseHelper::success([], null, 'Users imported successfully');
        });
    }

    public function showAttendanceLogs(Request $request)
    {
        $result = User::query()
            ->where('branch_id', $request->branch_id)
            ->with('department')->with('attendance')->get()->toArray();
        return ResponseHelper::success(
            $result
        );
    }

    public function DayAttendance(Request $request, $date)
    {
        $users = User::query()
            ->where('branch_id', $request->branch_id)
            ->whereNot('role', 'admin')
            ->with('department', 'userInfo')
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
