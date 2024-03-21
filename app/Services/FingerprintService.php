<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Decision;
use App\Models\Late;
use App\Models\Policy;
use App\Models\User;
use Carbon\Carbon;

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


    public function storeUserDelays($logPin, $branch_id, $checkInDate)
    {
        $thisUser = User::query()->where('id', $logPin)->first();
        $userPolicy = Policy::query()->where('branch_id', $thisUser->branch_id)->first();
        if ($userPolicy != null) {
            $checkInDate = substr($checkInDate, 0, 10);
            $attendanceExistence = Attendance::query()
                ->where('pin', $logPin)
                ->whereRaw('DATE(datetime) = ? ', [$checkInDate])//check the day
                ->where('status', '0')
                ->exists();
            if ($attendanceExistence) {
                $lateExistence = Late::query()
                    ->where('user_id', $thisUser->id)
                    ->whereRaw('DATE(lateDate) = ? ', [$checkInDate])
                    ->exists();
                if (!$lateExistence) {
                    $attendance = Attendance::query()
                        ->where('pin', $thisUser->pin)
                        ->whereRaw('DATE(datetime) = ? ', [$checkInDate])
                        ->where('status', '0')
                        ->first();
                    // $checkInDate = substr($attendance->datetime, 0, 10);
                    $checkInHour = substr($attendance->datetime, 11, 15);
                    $parsedHour = Carbon::parse($checkInHour);
                    $companyStartTime = $userPolicy->work_time['start_time'];
                    if ($parsedHour->isAfter($companyStartTime)) {
                        $diffLate = $parsedHour->diff($companyStartTime);
                        $hoursLate = $diffLate->format('%H.%I');
                        $lateData = [
                            'user_id' => $thisUser->id,
                            'lateDate' => $checkInDate,
                            // 'end' => $checkOutHour,
                            'check_in' => $attendance->datetime,
                            //'check_out' => $log['Status'] == 1 ? $checkOutHour : null,
                            'hours_num' => $hoursLate,
                        ];
                        if ($thisUser->branch_id == $branch_id && $userPolicy->deduction_status) {
                            $newLateData = [
                                'isPaid' => false,
                                'demands_compensation' => $userPolicy->demands_compensation,
                            ];
                            $mergedData = array_merge($lateData, $newLateData);
                            ///cal it here
                            $this->autoDeduction($thisUser, $attendance->datetime, 'warning', $lateData['hours_num']);
                            $this->autoDeduction($thisUser, $attendance->datetime, 'deduction', $lateData['hours_num']);
                            ///
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
                        }
                    }
                }
            }
        }


        ///////////////////////////////////////////////
//        $attendance = Attendance::query()
//            ->where('pin', $thisUser->pin)
//            ->whereRaw('DATE(datetime) = ? ', [$formattedDateTime])
//            ->where('status', '0')
//            ->first();

        //***
        // the first of check the late
//        $checkInDate = substr($attendance->datetime, 0, 10);
//        $checkInHour = substr($attendance->datetime, 11, 15);
//        $parsedHour = Carbon::parse($checkInHour);
//        //$policy = Policy::query()->where('branch_id', $branch_id)->first();
//        $userPolicy = Policy::query()->where('branch_id', $thisUser->branch_id)->first();
//        if ($userPolicy != null) {
//            $companyStartTime = $userPolicy->work_time['start_time'];
//            // check if the person late
//            if (($parsedHour->isAfter($companyStartTime) && $attendance->status == 0)
//            ) {
//                $diffLate = $parsedHour->diff($companyStartTime);
//                $hoursLate = $diffLate->format('%H.%I');
//                /*/?/*/
////                $thisUser = User::query()->where('id', ($log['PIN']))->first();
//                //$userId = $thisUser->id;
////                $userPolicy = Policy::query()->where('branch_id', $thisUser->branch_id)->first();
//                $lateData = [
//                    'user_id' => $userId,
//                    'lateDate' => $checkInDate,
//                    'end' => $checkOutHour,
//                    'check_in' => $log['Status'] == 0 ? $checkInHour : null,
//                    'check_out' => $log['Status'] == 1 ? $checkOutHour : null,
//                    'hours_num' => $log['Status'] == 1 ? $hoursOverTime : $hoursLate,
//                ];
//                if ($thisUser->branch_id == $branch_id && $userPolicy->deduction_status) {
//                    $newLateData = [
//                        'isPaid' => false,
//                        'demands_compensation' => $userPolicy->demands_compensation,
//                    ];
//                    $mergedData = array_merge($lateData, $newLateData);
//                    ///cal it here
//                    $this->fingerprintService->autoDeduction($thisUser, $log['DateTime'], 'Warning', $lateData['hours_num']);
//                    $this->fingerprintService->autoDeduction($thisUser, $log['DateTime'], 'Deduction', $lateData['hours_num']);
//                    ///
//                }
//                if ($thisUser->branch_id == $branch_id && !$userPolicy->deduction_status) {
//                    $newLateData = [
//                        'isPaid' => true,
//                        'demands_compensation' => $userPolicy->demands_compensation,
//                    ];
//                    $mergedData = array_merge($lateData, $newLateData);
//                }
//                if ($userId) {
//                    Late::query()->create($mergedData);
//                }


        //            }
//        }
    }

}
