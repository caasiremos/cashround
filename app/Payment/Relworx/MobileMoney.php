<?php

namespace App\Payment\Relworx;

use App\Enums\TransactionTypeEnum;
use App\Models\MomoTransaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Utils\Logger;
use Illuminate\Support\Facades\Http;

class MobileMoney
{
    private const INITIATE_COLLECTION_URL = "https://payments.relworx.com/api/mobile-money/request-payment";
    private const INITIATE_DISBURSEMENT_URL = "https://payments.relworx.com/api/mobile-money/send-payment";
    private const GET_TRANSACTION_STATUS_URL = "https://payments.relworx.com/api/mobile-money/check-request-status";
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.relworx.api_key');
    }

    public static function initiateCollection(string $reference, string $msisdn, int $amount)
    {
        $params = [
            'account_no' => config('services.relworx.business_account'),
            'reference' => $reference,
            'msisdn' => "+".$msisdn,
            'currency' => "UGX",
            'amount' => $amount,
            'description' => "Wallet Deposit"
        ];
        Logger::info('Relworx initiate collection params: ' . json_encode($params));
        $response = Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.relworx.api_key')
            ],
        )->post(self::INITIATE_COLLECTION_URL, $params)->json();
        Logger::info('Relworx initiate collection response', $response);
        return $response;
    }

    public static function initiateDisbursement(string $reference, int $msisdn, int $amount)
    {
        $params = [
            'account_no' => config('services.relworx.business_account'),
            'reference' => $reference,
            'msisdn' => "+".$msisdn,
            'currency' => "UGX",
            'amount' => $amount,
            'description' => "Wallet Withdrawal"
        ];
        Logger::info('Relworx initiate disbursement params: ' . json_encode($params));
        $response = Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.relworx.api_key')
            ],
        )->post(self::INITIATE_DISBURSEMENT_URL, $params)->json();
        Logger::info('Relworx initiate disbursement response', $response);
        return $response;
    }

    public static function getTransactionStatus(string $internalReference)
    {
        $transactions = MomoTransaction::where('internal_id', $internalReference)->get();
        foreach ($transactions as $transaction) {
            if ($transaction->internal_status === MomoTransaction::STATUS_PENDING) {
            $response = Http::asJson()->withHeaders(
                [
                    'Accept' => 'application/vnd.relworx.v2',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . config('services.relworx.api_key')
                ],
            )->get(self::GET_TRANSACTION_STATUS_URL, ['internal_reference' => $transaction->internal_id, 'account_no' => config('services.relworx.business_account')])->json();
                if ($response['success']) {
                    $transaction->internal_status = $response['status'];
                    $transaction->external_status = $response['status'];
                    $transaction->save();
                    if($transaction->transaction_type === TransactionTypeEnum::DEPOSIT->value) {
                        Wallet::where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
                    } else {
                        Wallet::where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
                    }
                }
            }
        }
    }
}