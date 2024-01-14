<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CalendarRequest extends FormRequest
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
            'title' => ['required','string','between:10,100'],
            'description' =>['required','string','between:10,255'],
            'start'=>['required', 'date','after_or_equal:today'],
            'end'=>['required', 'date','after_or_equal:start_date'],
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
