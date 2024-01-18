<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendRequest extends FormRequest
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
            'user_id' => ['required','integer','exists:users,id'],
            'startDate' =>['nullable','date','after_or_equal:today'],
            'endDate' =>['nullable','date','after_or_equal:startDate'],
            'duration' =>['string','in:daily,hourly'],
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
