<?php

namespace App\Repositories;

use App\Enums\TransactionTypeEnum;
use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\GroupRole;
use App\Models\Member;
use App\Models\TransactionAuth;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletTransactionRepository
{
    public function __construct(
        private GroupRotationRepository $groupRotationRepository,
    ) {}

    /**
     * Create a new wallet transaction for a member to member transfer
     */
    public function memberToMember(array $data): WalletTransaction
    {
        $sourceWallet = auth('members')->user()->wallet;
        $destinationWallet = Wallet::query()->where('account_number', $data['account_number'])->first();
        if (! $destinationWallet) {
            throw new ExpectedException('Account number not found');
        }

        if ($sourceWallet->id === $destinationWallet->id) {
            throw new ExpectedException('You cannot transfer money to yourself');
        }

        if ($sourceWallet->balance < $data['amount'] + WalletTransaction::MEMBER_TO_MEMBER_FEE) {
            throw new ExpectedException('Insufficient balance');
        }

        return DB::transaction(function () use ($sourceWallet, $destinationWallet, $data) {
            $transaction = WalletTransaction::create([
                'source_wallet_id' => $sourceWallet->id,
                'destination_wallet_id' => $destinationWallet->id,
                'member_id' => auth('members')->user()->id,
                'transaction_type' => TransactionTypeEnum::MEMBER_TO_MEMBER->value,
                'amount' => $data['amount'],
                'service_fee' => WalletTransaction::MEMBER_TO_MEMBER_FEE,
                'status' => WalletTransaction::STATUS_SUCCESSFUL,
                'transaction_id' => Str::uuid()->toString(),
            ]);

            Wallet::where('id', $sourceWallet->id)->decrement('balance', $data['amount'] + WalletTransaction::MEMBER_TO_MEMBER_FEE);
            Wallet::where('id', $destinationWallet->id)->increment('balance', $data['amount']);

            return $transaction;
        });
    }

    /**
     * Create a new wallet transaction for a group to member transfer
     */
    public function groupToMember(array $data): WalletTransaction
    {
        $member = Member::query()->where('id', $data['member_id'])->first();
        $destinationWallet = Wallet::query()->where('id', $member->wallet->id)->first();
        if (! $destinationWallet) {
            throw new ExpectedException('Destination wallet not found');
        }

        $sourceWallet = Wallet::query()->where('account_number', $data['account_number'])->first();
        if (! $sourceWallet) {
            throw new ExpectedException('Source wallet not found');
        }

        if ($sourceWallet->balance < $data['amount']) {
            throw new ExpectedException('Insufficient balance');
        }

        if ($destinationWallet->id === $sourceWallet->id) {
            throw new ExpectedException('You cannot transfer money to yourself');
        }

        $transaction = WalletTransaction::create([
            'source_wallet_id' => $sourceWallet->id,
            'destination_wallet_id' => $destinationWallet->id,
            'member_id' => $data['member_id'],
            'transaction_type' => TransactionTypeEnum::GROUP_TO_MEMBER->value,
            'amount' => $data['amount'],
            'service_fee' => 0,
            'status' => 'pending',
            'transaction_id' => Str::uuid()->toString(),
        ]);

        if ($sourceWallet?->group_id) {
            TransactionAuth::create([
                'wallet_transaction_id' => $transaction->id,
                'group_id' => $sourceWallet->group_id,
                'has_chairperson_approved' => false,
                'has_treasurer_approved' => false,
                'has_secretary_approved' => false,
                'status' => 'pending',
            ]);
        }

        return $transaction;
    }

    /**
     * Confirm a group-to-member transaction. Payload: member_id, role, group_id, wallet_transaction_id.
     * When all approval roles (chairperson, treasurer, secretary) in the group have confirmed,
     * the wallet transaction and transaction auth are marked as successful.
     */
    public function confirmGroupToWalletTransfer(array $data): TransactionAuth
    {
        $transactionAuth = TransactionAuth::where('wallet_transaction_id', $data['wallet_transaction_id'])
            ->where('group_id', $data['group_id'])
            ->where('status', 'pending')
            ->firstOrFail();

        $memberId = $data['member_id'];
        $role = strtolower($data['role']);

        $groupRole = GroupRole::where('group_id', $data['group_id'])
            ->where('member_id', $memberId)
            ->where('role', $role)
            ->firstOrFail();

        $updateData = match ($role) {
            GroupRole::CHAIRPERSON => ['has_chairperson_approved' => true],
            GroupRole::TREASURER => ['has_treasurer_approved' => true],
            GroupRole::SECRETARY => ['has_secretary_approved' => true],
            default => throw new \InvalidArgumentException("Role {$role} is not an approval role."),
        };

        $transactionAuth->update($updateData);

        $requiredRoles = [GroupRole::CHAIRPERSON, GroupRole::TREASURER, GroupRole::SECRETARY];
        $existingApprovalRoles = GroupRole::where('group_id', $data['group_id'])
            ->whereIn('role', $requiredRoles)
            ->pluck('role')
            ->unique()
            ->values()
            ->toArray();

        $allConfirmed = collect($existingApprovalRoles)->every(function ($r) use ($transactionAuth) {
            return match ($r) {
                GroupRole::CHAIRPERSON => $transactionAuth->has_chairperson_approved,
                GroupRole::TREASURER => $transactionAuth->has_treasurer_approved,
                GroupRole::SECRETARY => $transactionAuth->has_secretary_approved,
                default => true,
            };
        });

        if ($allConfirmed) {
            $wt = $transactionAuth->walletTransaction;
            $amount = (float) $wt->amount;

            DB::transaction(function () use ($transactionAuth, $wt, $amount) {
                Wallet::whereId($wt->source_wallet_id)->decrement('balance', $amount);
                Wallet::whereId($wt->destination_wallet_id)->increment('balance', $amount);
                $transactionAuth->update(['status' => 'successful']);
                $wt->update(['status' => 'successful']);

                $this->advanceRotationIfCurrentRecipient($wt);
            });
        }

        return $transactionAuth->fresh();
    }

    /**
     * After a successful group-to-member transfer, advance rotation if the recipient was the current rotation recipient.
     */
    private function advanceRotationIfCurrentRecipient(WalletTransaction $wt): void
    {
        $sourceWallet = Wallet::find($wt->source_wallet_id);
        if (! $sourceWallet?->group_id) {
            return;
        }

        $group = Group::find($sourceWallet->group_id);
        if (! $group) {
            return;
        }

        $state = $this->groupRotationRepository->getRotationState($group);
        $currentMember = $state['current_member'];
        if (! $currentMember) {
            return;
        }

        if ((int) $currentMember->id === (int) $wt->member_id) {
            $this->groupRotationRepository->advanceRotation($group);
        }
    }

    /**
     * Create a new wallet transaction for a member to group transfer
     */
    public function memberToGroup(array $data): WalletTransaction
    {
        $sourceWallet = auth('members')->user()->wallet;
        $destinationWallet = Wallet::query()->where('account_number', $data['account_number'])->first();
        if (! $destinationWallet) {
            throw new ExpectedException('Account number not found');
        }
        if ($sourceWallet->id === $destinationWallet->id) {
            throw new ExpectedException('You cannot transfer money to yourself');
        }

        if ($sourceWallet->balance < $data['amount'] + WalletTransaction::MEMBER_TO_GROUP_FEE) {
            throw new ExpectedException('Insufficient balance');
        }

        return DB::transaction(function () use ($sourceWallet, $destinationWallet, $data) {
            $transaction = WalletTransaction::create([
                'source_wallet_id' => $sourceWallet->id,
                'destination_wallet_id' => $destinationWallet->id,
                'member_id' => auth('members')->user()->id,
                'transaction_type' => TransactionTypeEnum::MEMBER_TO_GROUP->value,
                'amount' => $data['amount'],
                'service_fee' => 0,
                'status' => WalletTransaction::STATUS_PENDING,
                'transaction_id' => Str::uuid()->toString(),
            ]);

            Wallet::where('id', $sourceWallet->id)->decrement('balance', $data['amount'] + WalletTransaction::MEMBER_TO_GROUP_FEE);
            Wallet::where('id', $destinationWallet->id)->increment('balance', $data['amount']);

            return $transaction;
        });
    }

    /**
     * Get all transaction auths for a group
     */
    public function getGroupTransactionAuths(array $data): Collection
    {
        return TransactionAuth::where('group_id', $data['group_id'])->get();
    }

    /**
     * Get all transaction auths for a member
     */
    public function getMemberTransactionAuths(array $data): Collection
    {
        return TransactionAuth::where('member_id', $data['member_id'])->get();
    }

    /**
     * Get all wallet transactions for a group
     */
    public function getGroupWalletTransactions(array $data): Collection
    {
        return WalletTransaction::where('group_id', $data['group_id'])->get();
    }

    /**
     * Get all wallet transactions for a member
     */
    public function getMemberWalletTransactions(array $data): Collection
    {
        return WalletTransaction::where('member_id', $data['member_id'])->get();
    }
}
