<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class AbsenceRule implements ValidationRule
{
    private $absences;

    public function __construct(array $absences)
    {
        $this->absences = $absences;
    }

    public function validate($attribute, $value,$fail): void
    {
        $exists = DB::table('absences')
            ->where('user_id', $this->absences[0]['user_id'])
            ->where('startDate', $this->absences[0]['date'])
            ->exists();
        if ($exists) {
           $fail('Absence already exists for this user');
        }

    }
}

