<?php

namespace App\Http\Requests\PolicyRequest;

use App\Rules\WorkDaysRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePolicyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'work_time' => ['array','required'],
            // 'work_time.work_days'=>['array','required'],
            // 'work_time.work_days.*'=>['string',new WorkDaysRule],
            // 'work_time.start_time'=>['required','string'],
            // 'work_time.cut-off_time'=>['required','string'],
            // 'work_time.end_time'=>['required','string'],
            // 'work_time.notes'=>['array'],

            // 'annual_salary_increase' => ['required','array'],
            // 'annual_salary_increase.annual_salary_percentage'=>['integer','min:0'],
            // 'annual_salary_increase.allow_advance_request'=>['boolean'],
            // 'annual_salary_increase.notes'=>['array'],

            // 'warnings' => ['array','required'],
            // 'warnings.alerts_to_warnings'=>['integer','required'],
            // 'warnings.warnings_to_dismissal'=>['integer','required'],
            // 'warnings.notes'=>['array'],

            // 'absence_management' => ['required','array'],
            // 'absence_management.paid_absence_days'=>['required','array'],
            // 'absence_management.paid_absence_days.count'=>['required','integer'],
            // 'absence_management.paid_absence_days.compensatory_time'=>['required','boolean'],
            // 'absence_management.unpaid_absence_days'=>['required','array'],
            // 'absence_management.unpaid_absence_days.count'=>['required','integer'],
            // 'absence_management.unpaid_absence_days.compensatory_time'=>['required','boolean'],
            // 'absence_management.sick_absence_days'=>['required','array'],
            // 'absence_management.sick_absence_days.count'=>['required','integer'],
            // 'absence_management.sick_absence_days.compensatory_time'=>['required','boolean'],
            // 'absence_management.notes' => ['required','array'],

            // 'deduction_status'=>['required','boolean'],
            // 'rate_type'=>['array','sometimes'],

            // 'branch_id' => ['required', 'integer', 'exists:branches,id'],
            // 'demands_compensation'=>['required','boolean'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // $errors = $validator->errors();
        // $transformedErrors = [];
        // foreach ($errors->all() as $errorMessage) {
        //     $transformedErrors[] = $errorMessage;
        // }
        // throw new HttpResponseException(response()->json([
        //     'message' => 'Validation Error',
        //     'errors' => $transformedErrors,
        // ], 422));
    }
}
