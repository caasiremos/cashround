<?php

namespace App\Services;

use App\Models\WalletTransaction;
use App\Repositories\WalletTransactionRepository;

class WalletTransactionService
{
    public function __construct(private WalletTransactionRepository $walletTransactionRepository) {}

    /**
     * Create a new wallet transaction for a member to member transfer
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function memberToMember(array $data): WalletTransaction
    {
        return $this->walletTransactionRepository->memberToMember($data);
    }

    /**
     * Create a new wallet transaction for a group to member transfer
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function groupToMember(array $data): WalletTransaction
    {
        return $this->walletTransactionRepository->groupToMember($data);
    }

    /**
     * Create a new wallet transaction for a member to group transfer
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function memberToGroup(array $data): WalletTransaction
    {
        return $this->walletTransactionRepository->memberToGroup($data);
    }
}