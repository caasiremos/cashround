<?php

namespace App\Http\Requests;

use App\Exceptions\ExpectedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class GroupFormRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'frequency' => 'required|string|in:daily,weekly,monthly,yearly',
            'amount' => 'required|numeric|min:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExpectedException(implode(', ', $validator->errors()->all()));
    }
}
