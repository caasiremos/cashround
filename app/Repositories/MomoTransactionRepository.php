<?php

namespace App\Repositories;

use App\Enums\TransactionTypeEnum;
use App\Models\MomoTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class MomoTransactionRepository
{
    /**
     * Deposit money into the wallet
     *
     * @param array $data
     * @return MomoTransaction
     */
    public function deposit(array $data)
    {

        return MomoTransaction::create([
            'member_id' => auth('members')->user()->id,
            'amount' => $data['amount'],
            'phone_number' => $data['phone_number'],
            'transaction_type' => TransactionTypeEnum::DEPOSIT->value,
            'internal_status' => 'PENDING',
            'external_status' => 'PENDING',
            'external_id' => null,
            'provider_fee' => 0,
            'service_fee' => 0,
            'wallet_id' => auth('members')->user()->wallet->id,
            'internal_id' => Str::uuid()->toString(),
            'error_message' => null,
        ]);
    }
    /**
     * Withdraw money from the wallet
     *
     * @param array $data
     * @return MomoTransaction
     */
    public function withdrawal(array $data)
    {
        return MomoTransaction::create([
            'member_id' => auth('members')->user()->id,
            'amount' => $data['amount'],
            'phone_number' => $data['phone_number'],
            'transaction_type' => TransactionTypeEnum::WITHDRAWAL->value,
            'internal_status' => 'PENDING',
            'external_status' => 'PENDING',
            'external_id' => null,
            'provider_fee' => 0,
            'service_fee' => 0,
            'wallet_id' => auth('members')->user()->wallet->id,
            'internal_id' => Str::uuid()->toString(),
            'error_message' => null,
        ]);
    }

    /**
     * Get all momo transactions for a member
     *
     * @return Collection
     */
    public function getMemberMomoTransactions(): Collection
    {
        return MomoTransaction::where('member_id', auth('members')->user()->id)->get();
    }
}
