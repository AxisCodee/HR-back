<?php

namespace App\Services;

use App\Models\Decision;

class FingerprintService
{
    public $userService;

    public function __construct(UserServices $userService)
    {
        $this->userService = $userService;

    }

    public function autoDeduction($user, $date, $type): bool
    {
        $content = '';
        $amount = null;
        if ($type == 'Deduction') {
            $amount = $this->userService->employeeHourPrice($user);
            $content = 'Deduct ' . $amount . ' from ' . $user->first_name . ' ' . $user->last_name . ' Salary';
        }
        if ($type == 'Warning') {
            $content = 'Send a Warning to ' . $user->first_name . ' ' . $user->last_name . ' for arriving late at ' . $date;
        }
        if ($type == 'Absence') {
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
