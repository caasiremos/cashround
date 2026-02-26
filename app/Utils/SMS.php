<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

class SMS
{
    const URL = 'https://sms.wearemarz.com/api/v1/sms/send';

    public static function send($to, $message)
    {
        $apiKey = config('services.marze.api_key');
        $apiSecret = config('services.marze.api_secret');
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
            'Content-Type' => 'application/json',
        ])->post(self::URL, [
            'recipient' => '+'.$to,
            'message' => $message,
        ])->json();
        Logger::info('Marze SMS response: ' . json_encode($response));
        return $response;
    }
}
