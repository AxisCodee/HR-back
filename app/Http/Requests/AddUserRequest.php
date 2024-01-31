<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password as RulesPassword;

class AddUserRequest extends FormRequest
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
            'first_name', 'middle_name', 'last_name' => ['required', 'string', 'min:3', 'max:25'],
            'password' => ['required', 'string', RulesPassword::min(8)],
            'email' => ['required', 'email', 'unique:users,email,' . $this->id],
            'address' => ['required', 'string', 'min:3', 'max:25'],
            'birth_date' => ['required', 'date', 'before:today', 'date_format:Y-m-d'],
            'nationalID' => ['required', 'numeric', 'digits:11'],
            'health_status' => ['required', 'string', 'max:250'],
            'gender' => ['required', 'string', 'in:male,female'],
            'military_situation' => ['required', 'string', 'in:Postponed,Exempt,Finished'],
            'social_situation' => ['required', 'string', 'in:Single,Married'],
            'specialization' => ['required', 'string'],
            'contacts.emails.*.email' => ['email'],
            'contacts.phonenumbers.*' => ['numeric', 'digits:10'],
            'additional_files', 'emergency_contact' => ['nullable', 'array'],
            'certificates' => ['required', 'array'],
            'experiences' => ['required', 'array'],
            'educations' => ['required', 'array'],
            'skills' => ['required', 'array'],
            'languages' => ['required', 'array'],
            'secretaraits' => ['required', 'array'],
            'certificates.certificate*' => ['required', 'string'],
            'experiences.*' => ['required', 'string'],
            'educations.*.study' => ['required', 'string'],
            'educations.*.degree' => ['required', 'string'],
            'skills.*.skills' => ['required', 'string'],
            'languages.*.languages' => ['required', 'string'],
            'secretaraits.*.object' => ['required', 'string'],
            'level' => ['required', 'string', 'in:Senior,Junior,Mid'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'skills.*.rate' => ['required', 'integer'],
            'languages.*.rate' => ['required', 'integer'],
            'salary' => ['required', 'numeric'],
            'secretaraits.*.delivery_date' => ['required', 'date'],
            'contacts' => ['array', 'max:2'],
            'contacts.emails' => ['nullable', 'array'],
            'contacts.emails.*' => ['email', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
