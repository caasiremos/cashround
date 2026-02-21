<?php

namespace App\Payment;

use App\Utils\Logger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MtnMomoGateWay
{
    const BASE_URL = 'https://proxy.momoapi.mtn.com/';

    private const COLLECTION_AUTHORIZATION_API_TOKEN_URL = self::BASE_URL.'collection/token/';

    private const DISBURSEMENT_AUTHORIZATION_API_TOKEN_URL = self::BASE_URL.'disbursement/token/';

    private const COLLECTION_INITIATION_URL = self::BASE_URL.'collection/v1_0/requesttopay';

    private const DISBURSEMENT_INITIATION_URL = self::BASE_URL.'disbursement/v1_0/transfer';

    /**
     * Get authorization token for api authentication
     */
    public function getDisbursementToken()
    {
        return Http::asJson()->withHeaders([
            'Ocp-Apim-Subscription-Key' => self::disbursementKey(),
            'Authorization' => 'Basic '.base64_encode(self::apiUser().':'.self::apiKey()),
        ])->post(self::DISBURSEMENT_AUTHORIZATION_API_TOKEN_URL)
            ->json();
    }

    /**
     * Get authorization token for api authentication
     */
    public function getCollectionToken()
    {
        return Http::asJson()->withHeaders([
            'Ocp-Apim-Subscription-Key' => self::collectionKey(),
            'Authorization' => 'Basic '.base64_encode(self::apiUser().':'.self::apiKey()),
        ])->post(self::COLLECTION_AUTHORIZATION_API_TOKEN_URL)
            ->json();
    }

    /**
     * initiate customer pin prompt to complete a transaction
     */
    public function initiateCustomerCollection($phone_number, $amount): array
    {
        $externalId = self::referencesNumber();
        $token = $this->getCollectionToken()['access_token'];
        $request_body = [
            'amount' => strval($amount),
            'currency' => 'UGX',
            'externalId' => $externalId,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $phone_number,
            ],
            'payerMessage' => 'collection',
            'payeeNote' => 'collection',
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Reference-Id' => $externalId,
            'X-Target-Environment' => 'mtnuganda',
            'x-callback-url' => 'https://loangram.co.ug/api/callback/mtn',
            'Authorization' => 'Bearer '.$token,
            'Ocp-Apim-Subscription-Key' => self::collectionKey(),
        ])
            ->post(self::COLLECTION_INITIATION_URL, $request_body);

        Logger::info('COLLECTION_INITIATION_CODE '.'=>'.$response->status());
        Logger::info('EXTERNAL ID '.'=>'.$externalId);

        return [$externalId, $response->status()];
    }

    /**
     * Check transaction status
     */
    public function getCollectionTransactionStatus($reference): mixed
    {
        $token = $this->getCollectionToken()['access_token'];
        $response = Http::asJson()->withHeaders([
            'X-Target-Environment' => 'mtnuganda',
            'Ocp-Apim-Subscription-Key' => self::collectionKey(),
        ])->withToken($token)->get(self::COLLECTION_INITIATION_URL.'/'.$reference);
        Logger::info('COLLECTION TXN STATUS '.'=>'.$response->status());
        Logger::info('COLLECTION TXN BODY '.'=>'.$response->getBody()->getContents());

        return $response;
    }

    /**
     * Gets status of disbursement transaction
     */
    public function getDisbursementTransactionStatus($reference): mixed
    {
        $token = $this->getDisbursementToken()['access_token'];

        return Http::asJson()->withHeaders([
            'X-Target-Environment' => 'mtnuganda',
            'Ocp-Apim-Subscription-Key' => self::disbursementKey(),
        ])->withToken($token)->get(self::DISBURSEMENT_INITIATION_URL.'/'.$reference)
            ->json();
    }

    private static function referencesNumber(): string
    {
        return Str::uuid();
    }

    /**
     * Initiate mtn disbursements
     */
    public function initiateCustomerDisbursement($amount, $phone_number)
    {
        $reference = self::referencesNumber();
        $token = $this->getDisbursementToken()['access_token'];
        $request_body = $this->requestBody($amount, $reference, $phone_number);

        $response = Http::acceptJson()->withHeaders($this->headers($reference, $token))
            ->post(self::DISBURSEMENT_INITIATION_URL, $request_body);

        Logger::info('DISBURSEMENT_INITIATION_CODE'.'=>'.$response->status());
        Logger::info('DISBURSEMENT_INITIATION_BODY'.'=>'.$response->body());

        return $response->status();
    }

    private function headers($reference, $token): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-Reference-Id' => $reference,
            'x-callback-url' => 'https://loangram.co.ug/api/callback/mtn',
            'X-Target-Environment' => 'mtnuganda',
            'Authorization' => 'Bearer '.$token,
            'Ocp-Apim-Subscription-Key' => self::disbursementKey(),
        ];
    }

    /**
     * Construct the request body
     */
    private function requestBody($amount, $reference, $phone_number): array
    {
        return [
            'amount' => strval($amount),
            'currency' => 'UGX',
            'externalId' => $reference,
            'payee' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $phone_number,
            ],
            'payerMessage' => 'disbursement',
            'payeeNote' => 'disbursement',
        ];
    }

    private static function apiKey()
    {
        return config('services.telecom.mtn_api_key');
    }

    private static function apiUser()
    {
        return config('services.telecom.mtn_api_user');
    }

    private static function disbursementKey()
    {
        return config('services.telecom.mtn_disbursement_key');
    }

    private static function collectionKey()
    {
        return config('services.telecom.mtn_collection_key');
    }
}
