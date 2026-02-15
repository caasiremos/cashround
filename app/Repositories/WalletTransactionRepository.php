<?php

namespace App\Repositories;

use App\Enums\TransactionTypeEnum;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;

class WalletTransactionRepository
{
    /**
     * Create a new wallet transaction for a member to member transfer
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function memberToMember(array $data): WalletTransaction
    {
        return WalletTransaction::create([
            'source_wallet_id' => $data['source_wallet_id'],
            'destination_wallet_id' => $data['destination_wallet_id'],
            'member_id' => $data['member_id'],
            'transaction_type' => TransactionTypeEnum::MEMBER_TO_MEMBER->value,
            'amount' => $data['amount'],
            'service_fee' => $data['service_fee'],
            'status' => 'PENDING',
            'transaction_id' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Create a new wallet transaction for a group to member transfer
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function groupToMember(array $data): WalletTransaction
    {
        return WalletTransaction::create([
            'source_wallet_id' => $data['source_wallet_id'],
            'destination_wallet_id' => $data['destination_wallet_id'],
            'member_id' => $data['member_id'],
            'transaction_type' => TransactionTypeEnum::GROUP_TO_MEMBER->value,
            'amount' => $data['amount'],
            'service_fee' => $data['service_fee'],
            'status' => 'PENDING',
            'transaction_id' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Create a new wallet transaction for a member to group transfer
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function memberToGroup(array $data): WalletTransaction
    {
        return WalletTransaction::create([
            'source_wallet_id' => auth('members')->user()->wallet->id,
            'destination_wallet_id' => $data['destination_wallet_id'],
            'member_id' => $data['member_id'],
            'transaction_type' => TransactionTypeEnum::MEMBER_TO_GROUP->value,
            'amount' => $data['amount'],
            'service_fee' => 0,
            'status' => 'PENDING',
            'transaction_id' => Str::uuid()->toString(),
        ]);
    }
}