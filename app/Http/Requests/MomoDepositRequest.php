<?php

namespace App\Http\Requests;

use App\Exceptions\ExpectedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class MomoDepositRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;
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
            'amount' => 'required|numeric|min:1000',
            'phone_number' => 'required|string|max:255|regex:/^[0-9]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'The amount is required',
            'amount.numeric' => 'The amount must be a number',
            'amount.min' => 'The amount must be at least 1000',
            'phone_number.required' => 'The phone number is required',
            'phone_number.string' => 'The phone number must be a string',
            'phone_number.max' => 'The phone number must be less than 255 characters',
            'phone_number.regex' => 'The phone number must be a valid phone number',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExpectedException(implode(', ', $validator->errors()->all()));
    }
}
