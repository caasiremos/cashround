<?php

namespace App\Http\Requests;

use App\Exceptions\ExpectedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class GroupInviteRequest extends FormRequest
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
            'group_id' => 'required|exists:groups,id',
            'invite_code' => 'required|string|min:8|max:8',
        ];
    }
    public function messages(): array
    {
        return [
            'group_id.required' => 'The group id is required',
            'group_id.exists' => 'The group id is invalid',
            'invite_code.required' => 'The invite code is required',
            'invite_code.string' => 'The invite code must be a string',
            'invite_code.max' => 'The invite code must be less than 8 characters',
            'invite_code.min' => 'The invite code must be at least 8 characters',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExpectedException(implode(', ', $validator->errors()->all()));
    }
}
