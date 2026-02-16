<?php

namespace App\Http\Controllers;

use App\Http\Requests\MomoDepositRequest;
use App\Http\Responses\ApiSuccessResponse;
use App\Services\MomoTransactionService;
use Illuminate\Http\Request;

class MomoTransactionApiController extends Controller
{
    public function __construct(private MomoTransactionService $momoTransactionService) {}
    /**
     * Deposit money into the wallet
     *
     * @param MomoDepositRequest $request
     * @return ApiSuccessResponse
     */
    public function deposit(MomoDepositRequest $request)
    {
        $transaction = $this->momoTransactionService->deposit($request->all());

        return new ApiSuccessResponse($transaction, 'Deposit successful');
    }

    /**
     * Withdraw money from the wallet
     *
     * @param MomoWithdrawalRequest $request
     * @return ApiSuccessResponse
     */
    public function withdrawal(MomoDepositRequest $request)
    {
        $transaction = $this->momoTransactionService->withdrawal($request->all());
        return new ApiSuccessResponse($transaction, 'Withdrawal successful');
    }

    /**
     * Get all momo transactions for a member
     *
     * @return ApiSuccessResponse
     */
    public function getMemberMomoTransactions()
    {
        $transactions = $this->momoTransactionService->getMemberMomoTransactions();
        return new ApiSuccessResponse($transactions, 'Momo transactions fetched successfully');
    }
}
