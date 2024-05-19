<?php

namespace App\Services;

use App\Models\Absences;
use App\Models\Attendance;
use App\Models\Date;
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

    public function convertAndStoreAttendance($xml, $branchId)
    {
        $array = json_decode(json_encode($xml), true);
        $logsData = $array['Row'];
        $uniqueDates = [];
        foreach ($logsData as $log) {
            $this->storeAttendance($log, $branchId);
            $date = date('Y-m-d', strtotime($log['DateTime']));
            Date::updateOrCreate(['date' => $date, 'branch_id' => $branchId]);
            $checkInDate = substr($log['DateTime'], 0, 10);
            if (!in_array($checkInDate, $uniqueDates)) {
                $uniqueDates[] = $checkInDate;
            }
        }
        return $uniqueDates;
    }

    public function storeAttendance($log, $branchId)
    {
        $userLog = User::where('pin', intval($log['PIN']))
            ->where('branch_id', $branchId)
            ->first();
        $formattedDateTime = substr($log['DateTime'], 0, 10);
        $logExistence = Attendance::query()
            ->where('pin', $log['PIN'])
            ->where('branch_id', $branchId)
            ->whereRaw('DATE(datetime) = ? ', [$formattedDateTime])
            ->where('status', $log['Status'])
            ->exists();
        if (!$logExistence) {
            $attendanceObj = new Attendance();
            $attendanceObj->pin = intval($log['PIN']);
            $attendanceObj->datetime = $log['DateTime'];
            $attendanceObj->verified = $log['Verified'];
            $attendanceObj->status = $log['Status'];
            $attendanceObj->work_code = $log['WorkCode'];
            if ($userLog) {
                $attendanceObj->branch_id = $userLog->branch->id;
            } else {
                $attendanceObj->branch_id = $branchId;
            }
            $attendanceObj->save();
        }
    }

    public function storeUserDelays($logPin, $branchId, $checkDate, $status)
    {
        $thisUser = User::query()->where('pin', $logPin)
            ->where('branch_id', $branchId)
            ->first();
        if ($thisUser) {
            $userPolicy = Policy::query()->where('branch_id', $thisUser->branch_id)->first();
            if ($userPolicy != null) {
                $checkDate = substr($checkDate, 0, 10);
                $attendance = Attendance::query()
                    ->where('branch_id', $branchId)
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
                            $diffInMinutes = $parsedHour->diffInMinutes($companyStartTime, false);
                            if ($diffInMinutes >= 15) {
                                $diffLate = $parsedHour->diff($companyStartTime);
                                $hoursLate = $diffLate->format('%H.%I');
                                $this->storeUserLate(
                                    $thisUser,
                                    $checkDate,
                                    $hoursLate,
                                    $branchId,
                                    $userPolicy,
                                    $attendance->datetime,
                                    $status
                                );
                            }
                        }
                    }
                    if ($status == '1') {
                        $companyEndTime = $userPolicy->work_time['end_time'];
                        $this->checkUserOverTimes($thisUser->id, $attendance->datetime, $companyEndTime, $checkDate);
                        $lateExistence = Late::query()
                            ->where('user_id', $thisUser->id)
                            ->where('check_out', '!=', null)
                            ->whereRaw('DATE(lateDate) = ? ', [$checkDate])
                            ->exists();
                        if (!$lateExistence) {
                            $checkOutHour = substr($attendance->datetime, 11, 15);
                            $parsedHour = Carbon::parse($checkOutHour);
                            if ($parsedHour->isBefore($companyEndTime)) {
                                $diffLate = $parsedHour->diff($companyEndTime);
                                $hoursLate = $diffLate->format('%H.%I');
                                $this->storeUserLate(
                                    $thisUser,
                                    $checkDate,
                                    $hoursLate,
                                    $branchId,
                                    $userPolicy,
                                    $attendance->datetime,
                                    $status
                                );
                            }
                        }
                    }
                }
            }
        }

    }

    public function storeEnd($checkInDate, $pin, $branchId)
    {
        $checkOut = Attendance::query()
            ->where('pin', $pin)
            ->where('branch_id', $branchId)
            ->whereRaw('DATE(datetime) = ? ', [$checkInDate])
            ->where('status', '1')
            ->first();
        return $checkOut;
    }

    public function storeUserLate($user, $checkDate, $hoursLate, $branchId, $userPolicy, $attendance_datetime, $status)
    {
        $userId = $user->id;
        $userBranchId = $user->branch_id;
        $deductionStatus = $userPolicy->deduction_status;
        $compensation = $userPolicy->demands_compensation;
        $isPaid = !$deductionStatus;
        $type = $deductionStatus ? 'Unjustified' : 'justified';
        $checkTime = Carbon::parse($attendance_datetime)->format('H:i:s');
        $lateDate = $checkDate;
        if ($userBranchId == $branchId && $deductionStatus) {
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
                $end = $this->storeEnd($checkDate, $user->pin, $branchId);
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
                ->where('users.branch_id', $branch_id)
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
        $type = 'Unjustified';
        $isPaid = false;
        $demandsCompensation = $userPolicy->absence_management['paid_absence_days']['compensatory_time'];
        if ($user->branch_id == $branch_id && $userPolicy->deduction_status) { //Auto deduction
            $paidAbsenceDays = $userPolicy->absence_management['paid_absence_days']['count'];
            $count = $this->userAbsencesDaysCount($user->id, $date);
            if ($count > $paidAbsenceDays) {
                $demandsCompensation = $userPolicy->absence_management['unpaid_absence_days']['compensatory_time'];
                $this->autoDeduction($user, $date, 'Absence', 0);
                $this->autoDeduction($user, $date, 'Deduction', 0);
            }
            if ($count <= $paidAbsenceDays) {
                $isPaid = true;
            }
        }
        if ($user->branch_id == $branch_id && !$userPolicy->deduction_status) {
            $isPaid = true;
        }
        Absences::query()->create([
            'type' => $type,
            'isPaid' => $isPaid,
            'demands_compensation' => $demandsCompensation,
            'user_id' => $user->id,
            'startDate' => $date,
        ]);
        return true;
    }

    public function userAbsencesDaysCount($user_id, $checkDate)
    {
        //$startDate = Carbon::parse('2023-12-14');
        $startDate = Carbon::parse(Date::first()->date); //start fingerprint date
        $checkDate = Carbon::parse($checkDate);
        if ($checkDate->isAfter($startDate)) {
            return Absences::query()->where('user_id', $user_id)
                ->whereRaw('DATE(startDate) >= ? AND DATE(startDate) <= ?', [$startDate, $checkDate])
                ->count();
        }
        return 0;
    }

    public function clearDelays($branchId, $date)
    {
        $policy = Policy::query()->where('branch_id', $branchId)->first();
        if ($policy != null) {
            $companyEndTime = Carbon::parse($policy->work_time['end_time'])->format('H:i');
            $now = Carbon::now();
            if ($now->greaterThan($companyEndTime)) {
                Absences::query()->whereRaw('DATE(startDate) = ? ', [$date])
                    ->whereHas('users', function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    })
                    ->where('duration', 'hourly')
                    ->delete();
            }
        }
    }

    public function checkUserOverTimes($user_id, $attendanceDatetime, $companyEndTime, $checkDate)
    {
        $lateExistence = Late::query()
            ->where('user_id', $user_id)
            ->where('check_in', '==', null)
            ->where('check_out', '==', null)
            ->where('end', '!=', null)
            ->whereRaw('DATE(lateDate) = ? ', [$checkDate])
            ->exists();
        if (!$lateExistence) {
            $checkOutHour = substr($attendanceDatetime, 11, 15);
            $parsedHour = Carbon::parse($checkOutHour);
            if ($parsedHour->isAfter($companyEndTime)) {
                $diffLate = $parsedHour->diff($companyEndTime);
                $hoursOverTime = $diffLate->format('%H.%I');
                $this->storeUserOverTime(
                    $user_id,
                    $checkDate,
                    $hoursOverTime,
                    $checkOutHour
                );
            }
        }
    }

    public function storeUserOverTime($user_id, $checkDate, $hoursOverTime, $checkOutHour)
    {
        $overTime = Late::query()->create([
            'user_id' => $user_id,
            'lateDate' => $checkDate,
            'end' => $checkOutHour,
            'hours_num' => $hoursOverTime,
            'type' => 'justified'
        ]);
        return (bool)$overTime;
    }
}
