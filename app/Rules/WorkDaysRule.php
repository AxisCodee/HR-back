<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class WorkDaysRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate($attribute,$value,$fail): void
    {
        $Days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        if(!in_array($value,$Days)){
            $fail($value . ' is not a valid day');
        }
    }

    public function message()
    {
        return 'The day is not valid.';
    }
}
