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
        try {
            return DB::transaction(function () use ($request) {
                //store the attendance
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
                    $userLog = User::where('pin', intval($log['PIN']))->first();
                    $formattedDateTime = substr($log['DateTime'], 0, 10);
                    $logExistence = Attendance::query()
                        ->where('pin', $log['PIN'])
                        ->whereRaw('DATE(datetime) = ? ', [$formattedDateTime])                            //->whereDate('datetime', $formattedDateTime)
                        ->where('status', $log['Status'])
                        ->exists();
                    if (!$logExistence) {
                        if ($userLog) {
                            $attendance = [
                                'pin' => $log['PIN'],
                                'datetime' => $log['DateTime'],
                                'branch_id' => $userLog->branch_id,
                                'verified' => $log['Verified'],
                                'status' => $log['Status'],
                                'work_code' => $log['WorkCode'],
                            ];
                            Attendance::updateOrCreate(['datetime' => $log['DateTime'],
                                'branch_id' => $userLog->branch_id], $attendance);
                        }
                    }
                    $date = date('Y-m-d', strtotime($log['DateTime']));
                    Date::updateOrCreate(['date' => $date]);
                    // the first of check the late
                    $checkInDate = substr($log['DateTime'], 0, 10);
                    $checkInHour = substr($log['DateTime'], 11, 15);
                    $checkOutHour = substr($log['DateTime'], 11, 15);
                    $parsedHour = Carbon::parse($checkInHour);
                    $parsedHourOut = Carbon::parse($checkOutHour);
                    $policy = Policy::query()->where('branch_id', $branch->id)->first();
                    if ($policy != null) {
                        $companyStartTime = $policy->work_time['start_time'];
                        $companyEndTime = $policy->work_time['end_time'];
                        // check if the person late
                        if (($parsedHour->isAfter($companyStartTime) && $log['Status'] == 0) ||
                            ($parsedHourOut->isAfter($companyEndTime) && $log['Status'] == 1)
                        ) {
                            if ($log['Status'] == 1) {
                                $checkOutHour = substr($log['DateTime'], 11, 15);
                            }
                            if ($log['Status'] == 0) {
                                $checkInHour = substr($log['DateTime'], 11, 15);
                            }
                            $diffLate = $parsedHour->diff($companyStartTime);
                            $hoursLate = $diffLate->format('%H.%I');
                            $diffOverTime = $parsedHourOut->diff($companyEndTime);
                            $hoursOverTime = $diffOverTime->format('%H.%I');
                            $minutesLate = $parsedHour->diffInMinutes($companyStartTime);
                            $thisUser = User::query()->where('id', ($log['PIN']))->first();
                            $userId = $thisUser->id;
                            $userPolicy = Policy::query()->where('branch_id', $thisUser->branch_id)->first();
                            if ($userPolicy != null) {
                                $attendanceExistence = Attendance::query()
                                    ->where('pin', $log['PIN'])
                                    ->whereRaw('DATE(datetime) = ? ', [$formattedDateTime])                            //->whereDate('datetime', $formattedDateTime)
                                    ->where('status', '0')
                                    ->exists();
                                if (!$attendanceExistence) {
                                    $lateData = [
                                        'user_id' => $userId,
                                        'lateDate' => $checkInDate,
                                        'end' => $checkOutHour,
                                        'check_in' => $log['Status'] == 0 ? $checkInHour : null,
                                        'check_out' => $log['Status'] == 1 ? $checkOutHour : null,
                                        'hours_num' => $log['Status'] == 1 ? $hoursOverTime : $hoursLate,
                                    ];
                                    if ($thisUser->branch_id == $branch->id && $userPolicy->deduction_status) {
                                        $newLateData = [
                                            'isPaid' => false,
                                            'demands_compensation' => $userPolicy->demands_compensation,
                                        ];
                                        $mergedData = array_merge($lateData, $newLateData);
                                        ///cal it here
                                        $this->fingerprintService->autoDeduction($thisUser, $date, 'Warning');
                                        $this->fingerprintService->autoDeduction($thisUser, $date, 'Deduction');
                                        ///
                                    }
                                    if ($thisUser->branch_id == $branch->id && !$userPolicy->deduction_status) {
                                        $newLateData = [
                                            'isPaid' => true,
                                            'demands_compensation' => $userPolicy->demands_compensation,
                                        ];
                                        $mergedData = array_merge($lateData, $newLateData);
                                    }
                                    if ($userId) {
                                        Late::query()->create($mergedData);
                                    }
                                }
                            }
                        }
                    }
                    $checkInDate = substr($log['DateTime'], 0, 10);
                    if (!in_array($checkInDate, $uniqueDates)) {
                        $uniqueDates[] = $checkInDate;
                    }
                }
                // store the absence
                foreach ($uniqueDates as $date) {
                    $today = Carbon::now()->format('y-m-d');
                    // check if the date not today to do not store the absence
                    if (!Carbon::parse($today)->equalTo(Carbon::parse($date))) {
                        $usersWithoutAttendance = DB::table('users')
                            ->leftJoin('attendances', function ($join) use ($date) {
                                $join->on('users.pin', '=', 'attendances.pin')
                                    ->whereRaw('DATE(attendances.datetime) = ?', $date);
                            })
                            ->whereNull('attendances.pin')
                            ->select('users.*')
                            ->get();
                        // check if there ae an absence , to don't do the operation on null
                        if (!empty($usersWithoutAttendance)) {
                            //create the absence
                            foreach ($usersWithoutAttendance as $user) {
                                $userPolicy = Policy::query()->where('branch_id', $user->branch_id)->first();
                                if ($userPolicy != null) {
                                    $absence = DB::table('absences')
                                        ->where('user_id', $user->id)
                                        ->whereRaw('? BETWEEN startDate AND endDate', $date)
                                        ->first();
                                    if (!$absence) {//unjustified absence
                                        $userStartDate = UserInfo::query()->where('user_id', $user->id)
                                            ->exists();
                                        if ($userStartDate) {
                                            $absenceExistence = Absences::query()->where('user_id', $user->id)
                                                ->whereRaw('DATE(startDate) = ? ', [$date])
                                                ->exists();
                                            if (!$absenceExistence) {
                                                $userStartDate = UserInfo::query()->where('user_id', $user->id)->first();
                                                $startDate = Carbon::parse($userStartDate->start_date);
                                                $uDate = Carbon::parse($date);
                                                if ($startDate->lt($uDate)) {
                                                    if ($user->branch_id == $branch->id && $userPolicy->deduction_status) {//auto deduction
                                                        Absences::create(
                                                            ['startDate' => $date,
                                                                'user_id' => $user->id,
                                                                'type' => 'Unjustified',
                                                                'demands_compensation' => $userPolicy->demands_compensation,
                                                                'isPaid' => false,
                                                            ]);
                                                        //cal it here
                                                        $this->fingerprintService->autoDeduction($user, $date, 'Absence');
                                                        $this->fingerprintService->autoDeduction($user, $date, 'Deduction');

                                                    }
                                                    if ($user->branch_id == $branch->id && !$userPolicy->deduction_status) {
                                                        Absences::create(
                                                            ['startDate' => $date,
                                                                'user_id' => $user->id,
                                                                'type' => 'Unjustified',
                                                                'demands_compensation' => $userPolicy->demands_compensation,
                                                                'isPaid' => true,
                                                            ]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return ResponseHelper::success([], null, 'attendances logs stored successfully', 200);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error($e->validator->errors()->first(), 400);
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }

//        $branch_id = $request->branch_id;
//       dispatch(new StoreAttendanceLogsJob($branch_id));
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
