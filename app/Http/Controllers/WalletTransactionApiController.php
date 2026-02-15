<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletTransactionRequest;
use App\Http\Responses\ApiSuccessResponse;
use App\Services\WalletTransactionService;
use Illuminate\Http\Request;

class WalletTransactionApiController extends Controller
{
    public function __construct(private WalletTransactionService $walletTransactionService) {}

    /**
     * Create a new wallet transaction for a member to member transfer
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function memberToMember(WalletTransactionRequest $request): ApiSuccessResponse
    {
        $transaction = $this->walletTransactionService->memberToMember($request->all());
        return new ApiSuccessResponse($transaction, 'Wallet transaction created successfully');
    }

    /**
     * Create a new wallet transaction for a group to member transfer
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function groupToMember(WalletTransactionRequest $request): ApiSuccessResponse
    {
        $transaction = $this->walletTransactionService->groupToMember($request->all());
        return new ApiSuccessResponse($transaction, 'Wallet transaction created successfully');
    }

    /**
     * Create a new wallet transaction for a member to group transfer
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function memberToGroup(WalletTransactionRequest $request): ApiSuccessResponse
    {
        $transaction = $this->walletTransactionService->memberToGroup($request->all());
        return new ApiSuccessResponse($transaction, 'Wallet transaction created successfully');
    }
}
