<?php

namespace App\Http\Requests;

use App\Models\GroupRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmTransactionAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'role' => ['required', Rule::in([GroupRole::CHAIRPERSON, GroupRole::TREASURER, GroupRole::SECRETARY])],
            'group_id' => 'required|exists:groups,id',
            'wallet_transaction_id' => 'required|exists:wallet_transactions,id',
        ];
    }
}
