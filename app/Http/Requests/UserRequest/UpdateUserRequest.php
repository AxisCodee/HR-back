<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
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
            //Validation for the fields that will be inserted into the users table.
            'first_name' => ['sometimes', 'string', 'min:3'],
            'middle_name' => ['sometimes'],
            'last_name' => ['sometimes', 'string', 'min:3'],
            'email' => ['sometimes', 'email', 'min:10'],
            'role' => ['sometimes', 'exists:roles,name'],
            'specialization' => ['sometimes', 'string', 'min:3'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'password' => ['sometimes', 'string', 'min:4'],
            'pin' => ['sometimes', 'integer', 'min:1'],
            'address' => ['sometimes', 'string', 'min:3'],
            'branch_id' => ['sometimes', 'integer', 'exists:branches,id'],
            //Validation for the fields that will be inserted into the user_infos table.
            'salary' => ['sometimes', 'integer', 'min:1'],
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
