<?php

namespace App\Payment\Relworx;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Utils\Logger;
use Illuminate\Support\Facades\Http;

class MobileMoney
{
    private const INITIATE_COLLECTION_URL = "https://payments.relworx.com/api/mobile-money/request-payment";
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
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
        )->post(self::INITIATE_COLLECTION_URL, $params)->json();
        Logger::info('Relworx initiate collection response', $response);
        return $response;
    }

    public function getTransactionStatus()
    {
        $walletTransactions = WalletTransaction::where('transaction_status', 'pending')->get();
        foreach ($walletTransactions as $walletTransaction) {
            $response = Http::asJson()->withHeaders(
                [
                    'Accept' => 'application/vnd.relworx.v2',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey
                ],
            )->withQueryParams([
                'internal_reference' => $walletTransaction->external_reference,
                'account_no' => "REL08CACA5DDF"
            ])->get(self::GET_TRANSACTION_STATUS_URL)->json();

            Logger::info('Relworx get transaction status response', $response);

            if ($response['success']) {
                $walletTransaction->transaction_status = $response['status'];
                $walletTransaction->save();

                $wallet = Wallet::where('customer_id', $walletTransaction->customer_id)->first();
                if ($wallet) {
                    $wallet->balance += $walletTransaction->amount;
                    $wallet->save();
                }
            }
        }
    }
}