<?php

namespace App\Services;

use App\Models\Absences;
use App\Models\Attendance;
use App\Models\Decision;
use App\Models\Late;
use App\Models\Policy;
use App\Models\User;
use App\Models\UserInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FingerprintService
{
    public $userService;

    public function __construct(UserServices $userService)
    {
        $this->userService = $userService;

    }

    public function autoDeduction($user, $date, $type, $hoursNum): bool
    {
        $content = '';
        $amount = null;
        if ($type == 'deduction') {
            if ($hoursNum == 0) {
                $policy = Policy::query()->where('branch_id', $user->branch_id)->first();
                $hoursNum = $policy->monthlyhours;
            }
            $amount = $hoursNum * ($this->userService->employeeHourPrice($user));
            $content = 'Deduct ' . $amount . ' from ' . $user->first_name . ' ' . $user->last_name . ' Salary';
        }
        if ($type == 'warning') {
            $content = 'Send a Warning to ' . $user->first_name . ' ' . $user->last_name . ' for arriving late at ' . $date;
        }
        if ($type == 'absence') {
            $content = 'Record the absence of ' . $user->first_name . ' ' . $user->last_name . ' for ' . $date;
        }
        if ($content !== '') {
            Decision::query()->create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'type' => $type,
                'content' => $content,
                'amount' => $amount,
                'dateTime' => $date,
            ]);
            return true;
        }
        return false;
    }

    public function storeAttendance($log)
    {
        $userLog = User::where('pin', intval($log['PIN']))->first();
        $formattedDateTime = substr($log['DateTime'], 0, 10);
        $logExistence = Attendance::query()
            ->where('pin', $log['PIN'])
            ->whereRaw('DATE(datetime) = ? ', [$formattedDateTime])
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
    }

    public function storeUserDelays($logPin, $branch_id, $checkInDate)
    {
        $thisUser = User::query()->where('id', $logPin)->first();
        $userPolicy = Policy::query()->where('branch_id', $thisUser->branch_id)->first();
        if ($userPolicy != null) {
            $checkInDate = substr($checkInDate, 0, 10);
            $attendance = Attendance::query()
                ->where('pin', $thisUser->pin)
                ->whereRaw('DATE(datetime) = ? ', [$checkInDate])
                ->where('status', '0')
                ->first();
            if ($attendance != null) {
                $lateExistence = Late::query()
                    ->where('user_id', $thisUser->id)
                    ->whereRaw('DATE(lateDate) = ? ', [$checkInDate])
                    ->exists();
                if (!$lateExistence) {
                    // $checkInDate = substr($attendance->datetime, 0, 10);
                    $checkInHour = substr($attendance->datetime, 11, 15);
                    $parsedHour = Carbon::parse($checkInHour);
                    $companyStartTime = $userPolicy->work_time['start_time'];
                    if ($parsedHour->isAfter($companyStartTime)) {
                        $diffLate = $parsedHour->diff($companyStartTime);
                        $hoursLate = $diffLate->format('%H.%I');
                        $this->storeUserLate($thisUser, $checkInDate, $hoursLate,
                            $branch_id, $userPolicy, $attendance->datetime);
                    }
                }
            }
        }
    }

    public function storeEnd($checkInDate, $pin)
    {
        $checkOut = Attendance::query()
            ->where('pin', $pin)
            ->whereRaw('DATE(datetime) = ? ', [$checkInDate])
            ->where('status', '1')
            ->first();
        return $checkOut;

    }

    public function storeUserLate($thisUser, $checkInDate, $hoursLate, $branch_id, $userPolicy, $attendance_datetime)
    {
        $end = $this->storeEnd($checkInDate, $thisUser->pin);
        $lateData = [
            'user_id' => $thisUser->id,
            'lateDate' => $checkInDate,
            'check_in' => Carbon::parse($attendance_datetime)->format('H:i:s'),
            'hours_num' => $hoursLate,
        ];

        if ($thisUser->branch_id == $branch_id && $userPolicy->deduction_status) {
            $newLateData = [
                'isPaid' => false,
                'demands_compensation' => $userPolicy->demands_compensation,
            ];
            $mergedData = array_merge($lateData, $newLateData);
            $this->autoDeduction($thisUser, $attendance_datetime, 'warning', $lateData['hours_num']);
            $this->autoDeduction($thisUser, $attendance_datetime, 'deduction', $lateData['hours_num']);
        }
        if ($thisUser->branch_id == $branch_id && !$userPolicy->deduction_status) {
            $newLateData = [
                'isPaid' => true,
                'demands_compensation' => $userPolicy->demands_compensation,
            ];
            $mergedData = array_merge($lateData, $newLateData);
        }
        if ($thisUser->id) {
            Late::query()->create($mergedData);
            if ($end) {
                Late::query()
                    ->where('lateDate', $checkInDate)
                    ->where('user_id', $thisUser->id)
                    ->update(['end' => Carbon::parse($end->datetime)->format('H:i:s')]);
            }
        }
    }

    public function storeUserAbsences($date, $branch_id)
    {
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
                    $this->checkUserAbsences($user, $date, $branch_id);
                }
            }
        }
    }

    public function checkUserAbsences($user, $date, $branch_id)
    {
        $userPolicy = Policy::query()->where('branch_id', $user->branch_id)->first();
        if ($userPolicy != null) {
            $absence = DB::table('absences')
                ->where('user_id', $user->id)
                ->whereRaw('? BETWEEN startDate AND endDate', $date)
                ->first();
            if (!$absence) {
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
                            $this->storeAbsence($user, $date, $userPolicy, $branch_id);
                        }
                    }
                }
            }
        }
    }

    public function storeAbsence($user, $date, $userPolicy, $branch_id)
    {
        $absenceData = [
            'startDate' => $date,
            'user_id' => $user->id,
            'type' => 'Unjustified',
            'demands_compensation' => $userPolicy->demands_compensation,
        ];
        if ($user->branch_id == $branch_id && $userPolicy->deduction_status) {//auto deduction
            $newAbsenceData = [
                'isPaid' => false,
            ];
            $mergedData = array_merge($absenceData, $newAbsenceData);
            $this->autoDeduction($user, $date, 'Absence', 0);
            $this->autoDeduction($user, $date, 'Deduction', 0);
        }
        if ($user->branch_id == $branch_id && !$userPolicy->deduction_status) {
            $newAbsenceData = [
                'isPaid' => true,
            ];
            $mergedData = array_merge($absenceData, $newAbsenceData);
        }
        Absences::create($mergedData);
    }

    public function earlyhours($id,$date)
    {
        $user = User::with([
            'attendance' => function ($query) use ($date) {
                $query->filterDate($query, $date, 'datetime');
        }])->findOrFail($id);


    }
}
