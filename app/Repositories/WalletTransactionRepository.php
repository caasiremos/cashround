<?php

namespace App\Repositories;

use App\Enums\TransactionTypeEnum;
use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\GroupRole;
use App\Models\TransactionAuth;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\GroupRotationRepository;
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
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function memberToMember(array $data): WalletTransaction
    {
        $sourceWallet = auth('members')->user()->wallet;
        $destinationWallet = Wallet::find($data['account_number'])->first();
        if (! $destinationWallet) {
            throw new ExpectedException('Account number not found');
        }
        return DB::transaction(function () use ($sourceWallet, $destinationWallet, $data) {
            $transaction = WalletTransaction::create([
                'source_wallet_id' => $sourceWallet->id,
                'destination_wallet_id' => $destinationWallet->id,
                'member_id' => auth('members')->user()->id,
                'transaction_type' => TransactionTypeEnum::MEMBER_TO_MEMBER->value,
                'amount' => $data['amount'],
                'service_fee' => WalletTransaction::FEE_AMOUNT,
                'status' => WalletTransaction::STATUS_PENDING,
                'transaction_id' => Str::uuid()->toString(),
            ]);

            Wallet::where('id', $sourceWallet->id)->decrement('balance', $data['amount'] + WalletTransaction::FEE_AMOUNT);
            Wallet::where('id', $destinationWallet->id)->increment('balance', $data['amount']);

            return $transaction;
        });
    }

    /**
     * Create a new wallet transaction for a group to member transfer
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function groupToMember(array $data): WalletTransaction
    {
        $transaction = WalletTransaction::create([
            'source_wallet_id' => $data['source_wallet_id'],
            'destination_wallet_id' => $data['destination_wallet_id'],
            'member_id' => $data['member_id'],
            'transaction_type' => TransactionTypeEnum::GROUP_TO_MEMBER->value,
            'amount' => $data['amount'],
            'service_fee' => $data['service_fee'],
            'status' => 'pending',
            'transaction_id' => Str::uuid()->toString(),
        ]);

        $sourceWallet = Wallet::find($data['source_wallet_id']);
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
     *
     * @param array $data
     * @return TransactionAuth
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

    /**
     * Get all transaction auths for a group
     *
     * @param array $data
     * @return Collection
     */
    public function getGroupTransactionAuths(array $data): Collection
    {
        return TransactionAuth::where('group_id', $data['group_id'])->get();
    }

    /**
     * Get all transaction auths for a member
     *
     * @param array $data
     * @return Collection
     */
    public function getMemberTransactionAuths(array $data): Collection
    {
        return TransactionAuth::where('member_id', $data['member_id'])->get();
    }

    /**
     * Get all wallet transactions for a group
     *
     * @param array $data
     * @return Collection
     */
    public function getGroupWalletTransactions(array $data): Collection
    {
        return WalletTransaction::where('group_id', $data['group_id'])->get();
    }

    /**
     * Get all wallet transactions for a member
     *
     * @param array $data
     * @return Collection
     */
    public function getMemberWalletTransactions(array $data): Collection
    {
        return WalletTransaction::where('member_id', $data['member_id'])->get();
    }
}
