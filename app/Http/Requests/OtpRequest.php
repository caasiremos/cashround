<?php

namespace App\Http\Requests;

use App\Exceptions\ExpectedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
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
            'phone_number' => 'required|string|max:255|exists:otps,phone_number',
            'code' => 'required|string|max:255|exists:otps,code',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required',
            'phone_number.string' => 'Phone number must be a string',
            'phone_number.max' => 'Phone number must be less than 255 characters',
        ];
    }

     public function failedValidation(Validator $validator)
    {
        throw new ExpectedException(implode(', ', $validator->errors()->all()));
    }
}
