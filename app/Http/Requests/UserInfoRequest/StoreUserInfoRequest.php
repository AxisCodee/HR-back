<?php

namespace App\Http\Requests\UserInfoRequest;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserInfoRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'image' => ['required', 'mimes:jpg,bmp,png'],
            'birth_date' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'nationalID' => ['required', 'string', 'max:11'],
            'social_situation' => ['required', Rule::in(['Single', 'Married'])],
            'military_situation' => ['required', Rule::in(['Postponed', 'Exempt', 'Finished'])],
            'health_status' => ['required', 'string'],
            'salary' => ['required', 'integer'],
            'level' => ['required', Rule::in(['Senior', 'Mid', 'Junior'])],
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
