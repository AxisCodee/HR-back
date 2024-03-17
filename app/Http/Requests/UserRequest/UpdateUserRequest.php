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
            'first_name' => ['string', 'min:3'],
            'middle_name' => ['string', 'min:3'],
            'last_name' => ['string', 'min:3'],
            'email' => ['email', 'min:10'],
            'role' => ['exists:roles,id'],
            'specialization'=>['string','min:3'],
            'department_id' => ['exists:departments,id'],
            'password' => ['string', 'min:4'],
            'pin'=>['integer','min:1'],
            'address'=>['string','min:3'],
            'branch_id'=>['integer','exists:branches,id'],
        //Validation for the fields that will be inserted into the user_infos table.
            'salary'=>['integer','min:1'],
            
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
