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

    public function storeUserDelays($logPin, $branch_id, $checkDate, $status)
    {
        $thisUser = User::query()->where('id', $logPin)->first();
        $userPolicy = Policy::query()->where('branch_id', $thisUser->branch_id)->first();
        if ($userPolicy != null) {
            $checkDate = substr($checkDate, 0, 10);
            $attendance = Attendance::query()
                ->where('pin', $thisUser->pin)
                ->whereRaw('DATE(datetime) = ? ', [$checkDate])
                ->where('status', $status)
                ->first();
            if ($attendance != null) {
                if ($status == '0') {
                    $lateExistence = Late::query()
                        ->where('user_id', $thisUser->id)
                        ->where('check_in', '!=', null)
                        ->whereRaw('DATE(lateDate) = ? ', [$checkDate])
                        ->exists();
                    if (!$lateExistence) {
                        $checkInHour = substr($attendance->datetime, 11, 15);
                        $parsedHour = Carbon::parse($checkInHour);
                        $companyStartTime = $userPolicy->work_time['start_time'];
                        if ($parsedHour->isAfter($companyStartTime)) {
                            $diffLate = $parsedHour->diff($companyStartTime);
                            $hoursLate = $diffLate->format('%H.%I');
                            $this->storeUserLate($thisUser, $checkDate, $hoursLate,
                                $branch_id, $userPolicy, $attendance->datetime, $status);

                        }
                    }
                }
                if ($status == '1') {
                    $lateExistence = Late::query()
                        ->where('user_id', $thisUser->id)
                        ->where('check_out', '!=', null)
                        ->whereRaw('DATE(lateDate) = ? ', [$checkDate])
                        ->exists();
                    if (!$lateExistence) {
                        $checkOutHour = substr($attendance->datetime, 11, 15);
                        $parsedHour = Carbon::parse($checkOutHour);
                        $companyEndTime = $userPolicy->work_time['end_time'];
                        if ($parsedHour->isBefore($companyEndTime)) {
                            $diffLate = $parsedHour->diff($companyEndTime);
                            $hoursLate = $diffLate->format('%H.%I');
                            $this->storeUserLate($thisUser, $checkDate, $hoursLate,
                                $branch_id, $userPolicy, $attendance->datetime, $status);
                        }
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

    public function storeUserLate($user, $checkDate, $hoursLate, $branch_id, $userPolicy, $attendance_datetime, $status)
    {
        $userId = $user->id;
        $branchId = $user->branch_id;
        $deductionStatus = $userPolicy->deduction_status;
        $compensation = $userPolicy->demands_compensation;
        $isPaid = !$deductionStatus;
        $type = $deductionStatus ? 'Unjustified' : 'justified';
        $checkTime = Carbon::parse($attendance_datetime)->format('H:i:s');
        $lateDate = $checkDate;
        if ($branchId == $branch_id && $deductionStatus) {
            $this->autoDeduction($user, $attendance_datetime, 'warning', $hoursLate);
            $this->autoDeduction($user, $attendance_datetime, 'deduction', $hoursLate);
        }
        if ($userId) {
            $lateRecord = new Late();
            $lateRecord->user_id = $userId;
            $lateRecord->hours_num = $hoursLate;
            $lateRecord->isPaid = $isPaid;
            $lateRecord->demands_compensation = $compensation;
            $lateRecord->type = $type;
            if ($status == '0') {
                $lateRecord->check_in = $checkTime;
                $lateRecord->lateDate = $lateDate;
                $end = $this->storeEnd($checkDate, $user->pin);
                if ($end != null) {
                    Late::query()
                        ->where('lateDate', $checkDate)
                        ->where('user_id', $userId)
                        ->update(['end' => Carbon::parse($end->datetime)->format('H:i:s')]);
                }
            } else {
                $lateRecord->check_out = $checkTime;
                $lateRecord->lateDate = $lateDate;
            }
            $lateRecord->save();
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

    public function clearDelays($branch_id, $date)
    {
        $policy = Policy::query()->where('branch_id', $branch_id)->first();
        if ($policy != null) {
            $companyEndTime = Carbon::parse($policy->work_time['end_time'])->format('H:i');
            $now = Carbon::now();
            if ($now->greaterThan($companyEndTime)) {
                Absences::query()->whereRaw('DATE(startDate) = ? ', [$date])
                    ->where('duration', 'hourly')
                    ->delete();
            }
        }
    }

}
