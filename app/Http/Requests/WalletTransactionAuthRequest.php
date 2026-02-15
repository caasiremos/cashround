<?php

namespace App\Http\Requests;

use App\Exceptions\ExpectedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class WalletTransactionAuthRequest extends FormRequest
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
            //
            'wallet_transaction_id' => 'required|exists:wallet_transactions,id',
            'member_id' => 'required|exists:members,id',
            'role' => 'required|in:chairperson,treasurer,secretary',
            'group_id' => 'required|exists:groups,id',
        ];
    }

    public function messages(): array
    {
        return [
            'wallet_transaction_id.required' => 'The wallet transaction id is required.',
            'wallet_transaction_id.exists' => 'The wallet transaction id is invalid.',
            'member_id.required' => 'The member id is required.',
            'member_id.exists' => 'The member id is invalid.',
            'role.required' => 'The role is required.',
            'role.in' => 'The role is invalid.',
        ];
    }
    
    public function failedValidation(Validator $validator){
        throw new ExpectedException(implode(', ', $validator->errors()->all()));
    }
}
