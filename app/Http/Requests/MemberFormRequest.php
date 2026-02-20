<?php

namespace App\Http\Requests;

use App\Exceptions\ExpectedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class MemberFormRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required',
            'last_name.required' => 'The last name is required',
        ];
    }

     public function failedValidation(Validator $validator)
    {
        throw new ExpectedException(implode(', ', $validator->errors()->all()));
    }
}
