<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Absences;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Date;
use App\Models\Late;
use App\Models\Policy;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use TADPHP\TADFactory;

require 'tad\vendor\autoload.php';

class AttendanceController extends Controller
{
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

    public function storeAttendanceLogs()
    {
        try {
            return DB::transaction(function () {
                //store the attendence
                $branche = Branch::findOrFail(1); //it should be recieved (static temporary)
                $tad_factory = new TADFactory(['ip' => $branche->fingerprint_scanner_ip]);
                $tad = $tad_factory->get_instance();
                $all_user_info = $tad->get_all_user_info();
                $dt = $tad->get_date();
                $logs = $tad->get_att_log();
                $xml = simplexml_load_string($logs);
                $array = json_decode(json_encode($xml), true);
                $logsData = $array['Row'];
                $uniqueDates = [];
                foreach ($logsData as $log) {
                    $attendance = [
                        'pin' => $log['PIN'],
                        'datetime' => $log['DateTime'],
                        'verified' => $log['Verified'],
                        'status' => $log['Status'],
                        'work_code' => $log['WorkCode'],
                    ];
                    Attendance::updateOrCreate(['datetime' => $log['DateTime']], $attendance);
                    $date = date('Y-m-d', strtotime($log['DateTime']));
                    Date::updateOrCreate(['date' => $date]);
                    // the first of check the late
                    $checkInDate = substr($log['DateTime'], 0, 10);
                    $checkInHour = substr($log['DateTime'], 11, 15);
                    $checkOutHour = substr($log['DateTime'], 11, 15);
                    $parsedHour = Carbon::parse($checkInHour);
                    $parsedHourOut = Carbon::parse($checkOutHour);
                    $policy = Policy::query()->where('branch_id', $branche->id)->first();
                    $companyStartTime = $policy->work_time['start_time'];
                    $companyEndTime = $policy->work_time['end_time'];
                    // check if the persone late
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
                        $userId = User::query()->where('pin', ($log['PIN']))->value('id');
                        $lates = Late::query()
                            ->where('user_id', $userId)
                            ->whereDate('lateDate', '=', $checkInDate)
                            ->whereNull('check_in')
                            ->whereNull('check_out')
                            ->first();
                        if (!$lates) {
                            $newLateData = [
                                'user_id' => $userId,
                                'lateDate' => $checkInDate,
                                'check_in' => $log['Status'] == 0 ? $checkInHour : null,
                                'check_out' => $log['Status'] == 1 ? $checkOutHour : null,
                                'hours_num' => $log['Status'] == 1 ? $hoursOverTime : $hoursLate,
                            ];
                            if ($userId) {
                                $newLate = Late::query()->create($newLateData);
                            }
                        } else {
                            $lates->update([
                                'check_in' => $checkInHour,
                            ]);
                        }
                    }
                    // $numberOfHour = $lates->hours_num;
                    // if ($hoursLate > $numberOfHour) {
                    //     $moreLate = $hoursLate - $numberOfHour;
                    //     $lates->update(
                    //         [
                    //             'moreLate' => $moreLate,
                    //         ]
                    //     );
                    // }

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
                        // check if there ate an absence , to dont do the operation on null
                        if (!empty($usersWithoutAttendance)) {
                            //create the absence
                            foreach ($usersWithoutAttendance as $user) {
                                $absence = DB::table('absences')
                                    ->where('user_id', $user->id)
                                    ->whereRaw('? BETWEEN startDate AND endDate', $date)
                                    ->first();

                                if (!$absence) {
                                    Absences::updateOrCreate([
                                        'user_id' => $user->id,
                                        'startDate' => $date,

                                    ]);
                                }
                            }
                        }
                    }
                }
                return ResponseHelper::success([], null, 'attendaces logs stored successfully', 200);
            });
            return ResponseHelper::error('error', null);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error($e->validator->errors()->first(), 400);
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseHelper::error('QueryException', 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
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
        $users = User::with('department','userInfo')
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
