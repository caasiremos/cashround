<?php

namespace App\Repositories;

use App\Enums\TransactionTypeEnum;
use App\Exceptions\ExpectedException;
use App\Models\MomoTransaction;
use App\Models\Notification;
use App\Models\TransactionFee;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\FcmNotification;
use App\Payment\Relworx\MobileMoney;
use App\Utils\Logger;
use App\Utils\PhoneNumberUtil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MomoTransactionRepository
{
    /**
     * Deposit money into the wallet
     *
     * @param array $data
     * @return MomoTransaction
     */
    public function deposit(array $data)
    {
        $member = auth('members')->user();
        $wallet = $member->wallet;
        $amount = (float) $data['amount'];
        $phoneNumber = $data['phone_number'];
        $reference = Str::uuid()->toString();
        $response = MobileMoney::initiateCollection($reference, $phoneNumber, $amount);
        if ($response['success'] === true) {
            DB::transaction(function () use ($member, $wallet, $amount, $phoneNumber, $reference, $response) {
                $transaction = MomoTransaction::create([
                    'member_id' => $member->id,
                    'amount' => $amount,
                    'phone_number' => $phoneNumber,
                    'transaction_type' => TransactionTypeEnum::DEPOSIT->value,
                    'internal_status' => MomoTransaction::STATUS_PENDING,
                    'external_status' => MomoTransaction::STATUS_PENDING,
                    'external_id' => $response['internal_reference'],
                    'provider_fee' => 0,
                    'service_fee' => 0,
                    'telco_provider' => PhoneNumberUtil::provider($phoneNumber),
                    'wallet_id' => $wallet->id,
                    'internal_id' => $reference,
                    'error_message' => $response['message'] ?? null,
                ]);

                return $transaction;
            });
        } else {
            throw new ExpectedException('Failed initiating Mobile Money deposit');
        }
    }
    /**
     * Withdraw money from the wallet
     *
     * @param array $data
     * @return MomoTransaction
     */
    public function withdrawal(array $data)
    {
        $member = auth('members')->user();
        $wallet = $member->wallet;
        $amount = (float) $data['amount'];
        $phoneNumber = $data['phone_number'];
        $reference = Str::uuid()->toString();
        $serviceFee = $this->getServiceFee($amount, PhoneNumberUtil::provider($phoneNumber));
        $providerFee = $this->getProviderFee($amount, PhoneNumberUtil::provider($phoneNumber));
        $totalAmount = $amount + $serviceFee;
        if($totalAmount > $wallet->balance){
            throw new ExpectedException('Insufficient balance');
        }
        $response = MobileMoney::initiateDisbursement($reference, $phoneNumber, $amount);
        if ($response['success'] === true) {
            DB::transaction(function () use ($member, $wallet, $amount, $phoneNumber, $reference, $response, $serviceFee, $providerFee) {
                $transaction = MomoTransaction::create([
                    'member_id' => $member->id,
                    'amount' => $amount,
                    'phone_number' => $phoneNumber,
                    'transaction_type' => TransactionTypeEnum::WITHDRAWAL->value,
                    'internal_status' => MomoTransaction::STATUS_PENDING,
                    'external_status' => MomoTransaction::STATUS_PENDING,
                    'external_id' => $response['internal_reference'],
                    'provider_fee' => $providerFee,
                    'service_fee' => $serviceFee,
                    'telco_provider' => PhoneNumberUtil::provider($phoneNumber),
                    'wallet_id' => $wallet->id,
                    'internal_id' => $reference,
                    'error_message' => $response['message'] ?? null,
                ]);

                return $transaction;
            });
        } else {
            throw new ExpectedException('Failed initiating Mobile Money withdrawal');
        }
    }

    /**
     * Get all momo transactions for a member
     *
     * @return Collection
     */
    public function getMemberMomoTransactions(): Collection
    {
        return MomoTransaction::where('member_id', auth('members')->user()->id)->orderBy('created_at', 'DESC')->limit(3)->get();
    }

    /**
     * Get all wallet transactions for a member
     *
     * @return Collection
     */
    public function getMemberWalletTransactions(): Collection
    {
        return WalletTransaction::where('member_id', auth('members')->user()->id)->orderBy('created_at', 'DESC')->limit(3)->get();
    }


    /**
     * Relworx collection callback
     *
     * @param Request $request
     * @return void
     */
    public function relworxCollectionCallback(Request $request)
    {
        Logger::info(['RELWORX COLLECTION CALLBACK RESPONSE' => $request->all()]);
        if ($request->status === 'success') {
            DB::transaction(function () use ($request) {
                $momoTransaction = MomoTransaction::where('internal_id', $request->customer_reference)->first();
                if ($momoTransaction) {
                    $momoTransaction->internal_status = $request->status;
                    $momoTransaction->external_status = $request->status;
                    $momoTransaction->external_id = $request->internal_reference;
                    $momoTransaction->save();

                    Wallet::where('member_id', $momoTransaction->member_id)->increment('balance', $request->amount);
                    $notificationData = [
                        'title' => 'Wallet Deposit',
                        'body' => 'Your Wallet Deposit of ' . $request->amount . ' was successful.',
                        'data' => ['time' => now()],
                    ];
                    $momoTransaction->member->notify(new FcmNotification($notificationData));
                    Notification::create([
                        'member_id' => $momoTransaction->member_id,
                        'title' => 'Wallet Deposit Successful',
                        'body' => 'Your Wallet Deposit of UGX' . number_format($request->amount) . ' was successful.',
                    ]);
                } else {
                    $notificationData = [
                        'title' => 'Wallet Deposit Failed',
                        'body' => 'Your Wallet Deposit of UGX' . number_format($request->amount) . ' was failed.',
                        'data' => ['time' => now()],
                    ];
                    $momoTransaction->member->notify(new FcmNotification($notificationData));
                    Notification::create([
                        'member_id' => $momoTransaction->member_id,
                        'title' => 'Wallet Deposit',
                        'body' => 'Your Wallet Deposit of UGX' . number_format($request->amount) . ' failed to complete.',
                    ]);
                    throw new ExpectedException('Momo transaction not found');
                }
            });
        } else {
            throw new ExpectedException('Relworx collection callback failed');
        }
    }
    /**
     * Relworx disbursement callback
     *
     * @param Request $request
     * @return MomoTransaction
     */
    public function relworxDisbursementCallback(Request $request)
    {
        Logger::info(['RELWORX DISBURSEMENT CALLBACK RESPONSE' => $request->all()]);
        if ($request->status === 'success') {
            DB::transaction(function () use ($request) {
                $momoTransaction = MomoTransaction::where('internal_id', $request->customer_reference)->first();
                if ($momoTransaction) {
                    $revenueWallet = Wallet::where('account_number', 'like', 'CRR%')->first();
                    $revenueWallet->increment('balance', ($momoTransaction->service_fee + $momoTransaction->provider_fee));
                    $momoTransaction->internal_status = $request->status;
                    $momoTransaction->external_status = $request->status;
                    $momoTransaction->external_id = $request->internal_reference;
                    $momoTransaction->save();
                    Wallet::where('member_id', $momoTransaction->member_id)->decrement('balance', $request->amount);
                    $notificationData = [
                        'title' => 'Wallet Withdrawal',
                        'body' => 'Your Wallet Withdrawal of UGX' . number_format($request->amount) . ' was successful.',
                        'data' => ['time' => now()],
                    ];
                    $momoTransaction->member->notify(new FcmNotification($notificationData));
                    Notification::create([
                        'member_id' => $momoTransaction->member_id,
                        'title' => 'Wallet Withdrawal Successful',
                        'body' => 'Your Wallet Withdrawal of UGX' . number_format($request->amount) . ' was successful.',
                    ]);
                } else {
                    throw new ExpectedException('Momo transaction not found');
                    $notificationData = [
                        'title' => 'Wallet Withdrawal Failed',
                        'body' => 'Your Wallet Withdrawal of UGX' . number_format($request->amount) . ' was failed.',
                        'data' => ['time' => now()],
                    ];
                    $momoTransaction->member->notify(new FcmNotification($notificationData));
                    Notification::create([
                        'member_id' => $momoTransaction->member_id,
                        'title' => 'Wallet Withdrawal Failed',
                        'body' => 'Your Wallet Withdrawal of UGX' . number_format($request->amount) . ' failed to complete.',
                    ]);
                    throw new ExpectedException('Relworx disbursement callback failed');
                }
            });
        } else {
            throw new ExpectedException('Relworx disbursement callback failed');
        }
    }

    public function getServiceFee(int $amount, string $telcoProvider): int
    {
        return TransactionFee::where('provider', $telcoProvider)
            ->where('transaction_type', TransactionTypeEnum::WITHDRAWAL->value)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)->first()->service_fee; 
    }

    public function getProviderFee(int $amount, string $telcoProvider): int
    {
        return TransactionFee::where('provider', $telcoProvider)
            ->where('transaction_type', TransactionTypeEnum::WITHDRAWAL->value)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)->first()->provider_fee;
    }
}
