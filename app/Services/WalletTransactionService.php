<?php

namespace App\Services;

use App\Models\TransactionAuth;
use App\Models\WalletTransaction;
use App\Repositories\WalletTransactionRepository;
use Illuminate\Database\Eloquent\Collection;

class WalletTransactionService
{
    public function __construct(private WalletTransactionRepository $walletTransactionRepository) {}

    /**
     * Create a new wallet transaction for a member to member transfer
     */
    public function memberToMember(array $data): WalletTransaction
    {
        return $this->walletTransactionRepository->memberToMember($data);
    }

    /**
     * Create a new wallet transaction for a group to member transfer
     */
    public function groupToMember(array $data): WalletTransaction
    {
        return $this->walletTransactionRepository->groupToMember($data);
    }

    /**
     * Confirm a group-to-member transaction. When all approval roles have confirmed,
     * marks the wallet transaction and transaction auth as successful.
     */
    public function confirmGroupToWalletTransfer(array $data): TransactionAuth
    {
        return $this->walletTransactionRepository->confirmGroupToWalletTransfer($data);
    }

    /**
     * Create a new wallet transaction for a member to group transfer
     */
    public function memberToGroup(array $data): WalletTransaction
    {
        return $this->walletTransactionRepository->memberToGroup($data);
    }

    /**
     * Get all transaction auths for a group
     */
    public function getGroupTransactionAuths(array $data): Collection
    {
        return $this->walletTransactionRepository->getGroupTransactionAuths($data);
    }

    /**
     * Get all transaction auths for a member
     */
    public function getMemberTransactionAuths(array $data): Collection
    {
        return $this->walletTransactionRepository->getMemberTransactionAuths($data);
    }

    /**
     * Get all wallet transactions for a group
     */
    public function getGroupWalletTransactions(int $groupId): Collection
    {
        return $this->walletTransactionRepository->getGroupWalletTransactions($groupId);
    }

    /**
     * Get all wallet transactions for a member
     * @param array $data
     * @return Collection
     */
    public function getMemberWalletTransactions(array $data): Collection
    {
        return $this->walletTransactionRepository->getMemberWalletTransactions($data);
    }
}
