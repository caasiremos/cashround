<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionAuthRequest;
use App\Http\Requests\WalletTransactionAuthRequest;
use App\Http\Requests\WalletTransactionRequest;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Group;
use App\Services\WalletTransactionService;
use Illuminate\Http\Request;

class WalletTransactionApiController extends Controller
{
    public function __construct(private WalletTransactionService $walletTransactionService) {}

    /**
     * Create a new wallet transaction for a member to member transfer
     *
     * @param WalletTransactionRequest $request
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
     * @param  Request  $request
     */
    public function groupToMember(WalletTransactionRequest $request): ApiSuccessResponse
    {
        $transaction = $this->walletTransactionService->groupToMember($request->all());

        return new ApiSuccessResponse($transaction, 'Wallet transaction created successfully');
    }

    /**
     * Confirm a group-to-member transaction. Payload: member_id, role, group_id, wallet_transaction_id.
     * When all approval roles have confirmed, the transaction is marked successful.
     */
    public function confirmGroupToMember(TransactionAuthRequest $request): ApiSuccessResponse
    {
        $transactionAuth = $this->walletTransactionService->confirmGroupToWalletTransfer($request->all());

        return new ApiSuccessResponse($transactionAuth, 'Transaction confirmation recorded');
    }

    /**
     * Create a new wallet transaction for a member to group transfer
     *
     * @param  Request  $request
     */
    public function memberToGroup(WalletTransactionRequest $request): ApiSuccessResponse
    {
        $transaction = $this->walletTransactionService->memberToGroup($request->all());

        return new ApiSuccessResponse($transaction, 'Wallet transaction created successfully');
    }

    /**
     * Get all wallet transactions for a member
     */
    public function getMemberWalletTransactions(Request $request): ApiSuccessResponse
    {
        $transactions = $this->walletTransactionService->getMemberWalletTransactions($request->all());

        return new ApiSuccessResponse($transactions, 'Wallet transactions fetched successfully');
    }

    /**
     * Get all wallet transactions for a group
     */
    public function getGroupWalletTransactions(Group $group): ApiSuccessResponse
    {
        $transactions = $this->walletTransactionService->getGroupWalletTransactions($group->id);

        return new ApiSuccessResponse($transactions, 'Wallet transactions fetched successfully');
    }

    /**
     * Get all transaction auths for a group
     *
     * @param WalletTransactionAuthRequest $request
     * @return ApiSuccessResponse
     */
    public function getGroupTransactionAuths(WalletTransactionAuthRequest $request): ApiSuccessResponse
    {
        $transactionAuths = $this->walletTransactionService->getGroupTransactionAuths($request->all());

        return new ApiSuccessResponse($transactionAuths, 'Transaction auths fetched successfully');
    }

    /**
     * Get all transaction auths for a member
     */
    public function getMemberTransactionAuths(Request $request): ApiSuccessResponse
    {
        $transactionAuths = $this->walletTransactionService->getMemberTransactionAuths($request->all());

        return new ApiSuccessResponse($transactionAuths, 'Transaction auths fetched successfully');
    }
}
