<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserInfoRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'image' => 'required|string',
            'birth_date' => 'required|date',
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'nationalID' => 'required|string|max:11',
            'social_situation' => [
                'required',
                Rule::in(['Single', 'Married']),
            ],
            'study_situation' => 'required|array',
            'military_situation' => [
                'required',
                Rule::in(['Postponed', 'Exempt', 'Finished']),
            ],
            'certificates' => 'required|array',
            'salary' => 'required|integer',
        ];
    }
}
