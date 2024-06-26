<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password as RulesPassword;

class StoreUserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:3', 'max:25'],
            'middle_name' => ['string', 'max:25'],
            'last_name' => ['string', 'min:3', 'max:25'],
            'password' => ['required', 'string', RulesPassword::min(8)],
            //'pin' => ['numeric'],
            'role' => ['required', 'string', 'in:employee,admin'],
            'email' => ['required', 'email', 'unique:users,email,' . $this->id],
            // 'address' => ['required', 'string', 'min:3', 'max:25'],
            'birth_date' => ['required', 'date', 'before:today', 'date_format:Y-m-d'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'nationalID' => ['required', 'numeric', 'digits:11'],
            //'health_status' => ['required', 'string', 'max:250'],
            // 'gender' => ['required', 'string', 'in:Male,Female'],
            // 'social_situation' => ['required', 'string', 'in:Single,Married'],
            // 'specialization' => ['required', 'string'],
            // 'additional_files', 'emergency_contact' => ['nullable', 'array'],
            // 'certificates' => [ 'array'],
            // 'experiences' => ['array'],
            //'educations' => [ 'array'],
            // 'skills' => ['required', 'array'],
            // 'languages' => ['required', 'array'],
            'secretaraits' => ['array'],
            'certificates.certificate*' => ['string'],
            // 'experiences.*' => ['required', 'string'],
            //'educations.*.study' => ['string'],
            //'educations.*.degree' => ['string'],
            //'skills.*.skills' => ['required', 'string'],
            // 'languages.*.languages' => ['required', 'string'],
            // 'secretaraits.*.object' => ['required', 'string'],
            'level' => ['required', 'string', 'in:Senior,Junior,Mid'],
            // 'skills.*.rate' => ['integer'],
            'languages.*.rate' => ['integer'],
            'salary' => ['required', 'numeric'],
            // 'secretaraits.*.delivery_date' => ['date'],
            'contacts' => ['array', 'max:2'],
            'contacts.emails' => ['nullable', 'array'],
            'contacts.emails.*.email' => ['nullable', 'email', 'string'],
            'contacts.phonenumbers' => ['array'],
            'contacts.phonenumbers.*.phone_num' => ['numeric', 'digits:10'],
            //contract
            'contract' => ['array'],
            'contract.*.path' => ['mimes:pdf'],
            'contract.*.startTime' => ['date', 'date_format:Y-m-d'],
            'contract.*.endTime' => ['date', 'date_format:Y-m-d'],
            //
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $transformedErrors = [];
        foreach ($errors->all() as $errorMessage) {
            $transformedErrors[] = $errorMessage;
        }
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $transformedErrors,
        ], 422));
    }
}
