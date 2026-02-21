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

        return new ApiSuccessResponse($transaction, 'Mobile Money deposit initiated successfully, please wait for the pin prompt to complete the transaction');
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

    /**
     * Get all wallet transactions for a member
     *
     * @return ApiSuccessResponse
     */
    public function getMemberWalletTransactions()
    {
        $transactions = $this->momoTransactionService->getMemberWalletTransactions();
        return new ApiSuccessResponse($transactions, 'Momo transactions fetched successfully');
    }

    /**
     * Relworx collection callback
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function relworxCollectionCallback(Request $request)
    {
        $collection = $this->momoTransactionService->relworxCollectionCallback($request);
        return new ApiSuccessResponse($collection, 'Collection successful');
    }

    /**
     * Relworx disbursement callback
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function relworxDisbursementCallback(Request $request)
    {
        $disbursement = $this->momoTransactionService->relworxDisbursementCallback($request);
        return new ApiSuccessResponse($disbursement, 'Disbursement successful');
    }
}
