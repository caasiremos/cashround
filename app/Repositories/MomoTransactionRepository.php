<?php

namespace App\Repositories;

use App\Enums\TransactionTypeEnum;
use App\Models\MomoTransaction;
use App\Models\Notification;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\FcmNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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
        $member = auth('members')->user();
        $wallet = $member->wallet;
        $amount = (float) $data['amount'];
        DB::transaction(function () use ($member, $wallet, $amount, $data) {
            $transaction = MomoTransaction::create([
                'member_id' => $member->id,
                'amount' => $amount,
                'phone_number' => $data['phone_number'],
                'transaction_type' => TransactionTypeEnum::DEPOSIT->value,
                'internal_status' => 'PENDING',
                'external_status' => 'PENDING',
                'external_id' => null,
                'provider_fee' => 0,
                'service_fee' => 0,
                'wallet_id' => $wallet->id,
                'internal_id' => Str::uuid()->toString(),
                'error_message' => null,
            ]);

            Wallet::where('id', $wallet->id)->increment('balance', $amount);

            $notificationData = [
                'title' => 'Wallet Deposit',
                'body' => 'Your Wallet Deposit was successful.',
                'data' => ['time' => now()],
            ];
            $member->notify(new FcmNotification($notificationData));
            Notification::create([
                'member_id' => $member->id,
                'title' => 'Wallet Deposit',
                'body' => 'Your Wallet Deposit was successful.',
            ]);
            return $transaction;
        });
    }
    /**
     * Withdraw money from the wallet
     *
     * @param array $data
     * @return MomoTransaction
     */
    public function withdrawal(array $data)
    {
        $member = auth('members')->user();
        $wallet = $member->wallet;
        $amount = (float) $data['amount'];
        DB::transaction(function () use ($member, $wallet, $amount, $data) {
            $transaction = MomoTransaction::create([
                'member_id' => $member->id,
                'amount' => $amount,
                'phone_number' => $data['phone_number'],
                'transaction_type' => TransactionTypeEnum::WITHDRAWAL->value,
                'internal_status' => 'pending',
                'external_status' => 'pending',
                'external_id' => null,
                'provider_fee' => 0,
                'service_fee' => 0,
                'wallet_id' => $wallet->id,
                'internal_id' => Str::uuid()->toString(),
                'error_message' => null,
            ]);

            Wallet::where('id', $wallet->id)->decrement('balance', $amount);

            return $transaction;
        });
    }

    /**
     * Get all momo transactions for a member
     *
     * @return Collection
     */
    public function getMemberMomoTransactions(): Collection
    {
        return MomoTransaction::where('member_id', auth('members')->user()->id)->orderBy('created_at', 'DESC')->limit(3)->get();
    }

    /**
     * Get all wallet transactions for a member
     *
     * @return Collection
     */
    public function getMemberWalletTransactions(): Collection
    {
        return WalletTransaction::where('member_id', auth('members')->user()->id)->orderBy('created_at', 'DESC')->limit(3)->get();
    }
}
