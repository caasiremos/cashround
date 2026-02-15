<?php

namespace App\Http\Controllers;

use App\Http\Requests\MomoDepositRequest;
use Illuminate\Http\Request;

class MomoTransactionApiController extends Controller
{
    public function deposit(MomoDepositRequest $request)
    {
        $transaction = $this->momoTransactionService->deposit($request->user(), $validated['amount']);

        return new ApiSuccessResponse($transaction, 'Deposit successful');
    }
}
