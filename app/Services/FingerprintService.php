<?php

namespace App\Services;

use App\Models\Decision;
use App\Models\Policy;

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

}
