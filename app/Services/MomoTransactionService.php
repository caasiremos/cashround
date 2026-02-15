<?php

namespace App\Services;

use App\Repositories\MomoTransactionRepository;

class MomoTransactionService
{
    public function __construct(private MomoTransactionRepository $momoTransactionRepository) {}
    /**
     * Deposit money into the wallet
     *
     * @param array $data
     * @return MomoTransaction
     */
    public function deposit(array $data)
    {
        return $this->momoTransactionRepository->deposit($data);
    }

    /**
     * Withdraw money from the wallet
     *
     * @param array $data
     * @return MomoTransaction
     */
    public function withdrawal(array $data)
    {
        return $this->momoTransactionRepository->withdrawal($data);
    }
}
