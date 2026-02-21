<?php

namespace App\Payment;

use App\Utils\Logger;
use App\Utils\PhoneNumberUtil;
use App\Utils\Util;
use Illuminate\Support\Facades\Http;

class AirtelMoneyGateWay
{
    private const STAGING_BASE_URL = 'https://openapiuat.airtel.africa/';

    private const PROD_BASE_URL = 'https://openapi.airtel.africa/';

    private const AUTHORIZATION_END_POINT = self::PROD_BASE_URL.'auth/oauth2/token';

    private const PAYMENTS = self::PROD_BASE_URL.'merchant/v1/payments/';

    private const DISBURSEMENTS = self::PROD_BASE_URL.'standard/v1/disbursements/';

    private const PAYMENT_STATUS = self::PROD_BASE_URL.'standard/v1/payments/';

    public static function getAuthorizationToken()
    {
        $request_body = [
            'client_id' => config('services.telecom.airtel_client_id'),
            'client_secret' => config('services.telecom.airtel_client_secrete'),
            'grant_type' => config('services.telecom.airtel_grant_type'),
        ];

        return Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(self::AUTHORIZATION_END_POINT, $request_body)
            ->json();
    }

    /**
     * Initiates airtel money payments
     */
    public static function initiatePayments($amount, $phone_number): array
    {
        $access_token = self::getAuthorizationToken();
        $external_id = strval(self::referencesNumber());
        Logger::info(PhoneNumberUtil::formatAirtelPhoneNumber($phone_number));
        $request_body = [
            'reference' => 'Loangram',
            'subscriber' => [
                'country' => 'UG',
                'currency' => 'UGX',
                'msisdn' => PhoneNumberUtil::formatAirtelPhoneNumber($phone_number),
            ],
            'transaction' => [
                'amount' => $amount,
                'country' => 'UG',
                'currency' => 'UGX',
                'id' => $external_id,
            ],
        ];

        $payments = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => 'Bearer '.$access_token['access_token'],
        ])->post(self::PAYMENTS, $request_body)
            ->json();

        Logger::info($payments);

        return $payments;
    }

    public static function checkPaymentStatus($token, $external_id)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => 'Bearer '.$token,
        ])->get(self::PAYMENT_STATUS.$external_id)
            ->json();
    }

    public static function initiateDisbursement($amount, $phone_number)
    {
        $access_token = self::getAuthorizationToken();
        $request_body = [
            'payee' => [
                'msisdn' => PhoneNumberUtil::formatAirtelPhoneNumber($phone_number),
            ],
            'reference' => 'Loangram disbursements',
            'pin' => config('services.telecom.airtel_pin'),
            'transaction' => [
                'amount' => $amount,
                'id' => strval(self::referencesNumber()),
            ],
        ];

        Logger::info(['AIRTEL_DISBUREMENT_REQUEST_BODY' => $request_body]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => 'Bearer '.$access_token['access_token'],
        ])->post(self::DISBURSEMENTS, $request_body)
            ->json();
        Logger::info(['AIRTEL-DISBUREMENT-INITIATION' => $response]);

        return $response;
    }

    private static function referencesNumber()
    {
        return substr(str_shuffle('0123'.time()), 1, 16);
    }
}
