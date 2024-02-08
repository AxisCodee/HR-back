<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class UpdateUserInfoRequest extends FormRequest
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
            'image' => 'sometimes',
            'birth_date' => 'date',
            'gender' => [Rule::in(['Male', 'Female'])],
            'nationalID' => 'string|max:11',
            'social_situation' => [
                Rule::in(['Single', 'Married']),
            ],
            'military_situation' => [
                Rule::in(['Postponed', 'Exempt', 'Finished']),
            ],
            'health_status' => 'string',
            'salary' => 'integer',
            'level' => [
                Rule::in(['Senior', 'Mid', 'Junior'])
            ],
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
