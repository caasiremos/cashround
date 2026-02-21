<?php

namespace App\Services;

use App\Repositories\MomoTransactionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
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

    /**
     * Get all momo transactions for a member
     *
     * @return Collection
     */
    public function getMemberMomoTransactions(): Collection
    {
        return $this->momoTransactionRepository->getMemberMomoTransactions();
    }

    /**
     * Get all wallet transactions for a member
     *
     * @return Collection
     */
    public function getMemberWalletTransactions(): Collection
    {
        return $this->momoTransactionRepository->getMemberWalletTransactions();
    }

    /**
     * Relworx collection callback
     *
     * @param Request $request
     * @return MomoTransaction
     */
    public function relworxCollectionCallback(Request $request)
    {
        $this->momoTransactionRepository->relworxCollectionCallback($request);
    }

    /**
     * Relworx disbursement callback
     *
     * @param Request $request
     * @return MomoTransaction
     */
    public function relworxDisbursementCallback(Request $request)
    {
        return $this->momoTransactionRepository->relworxDisbursementCallback($request);
    }
}
