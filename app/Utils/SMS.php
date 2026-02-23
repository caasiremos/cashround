<?php

namespace App\Utils;

use AfricasTalking\SDK\AfricasTalking;

class SMS
{
    public static function send($to, $message)
    {
        $username = config('services.africastalking.username');
        $apiKey   = config('services.africastalking.api_key');
        $africasTalking = new AfricasTalking($username, $apiKey);
        $sms      = $africasTalking->sms();
        $response =  $sms->send([
            'to'      => $to,
            'message' => $message
        ]);
        return $response;
    }
}
