<?php

namespace App\Http\Requests\AbsencesRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\AbsenceRule;

class StoreAbsencesRequest extends FormRequest
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
        //dd($this->input('absence'));
        return [
            'absence.*.user_id' => ['integer', 'exists:users,id'],
            'absence.*.date' => ['required', 'date', new AbsenceRule($this->input('absence'))],
            'absence.*.type' => ['string', 'in:sick,justified,Unjustified'],
            'absence.*.isPaid' => ['boolean']
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
