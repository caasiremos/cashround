<?php

namespace App\Notifications;

use App\Services\Firebase\Services\FcmClient as ServicesFcmClient;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    protected $fcmClient;

    public function __construct()
    {
        $this->fcmClient = new ServicesFcmClient();
    }

    public function send($notifiable, Notification $notification)
    {
        $token = $notifiable->routeNotificationFor('fcm');
        if (empty($token)) {
            Log::warning('FCM skipped: no token for notifiable', ['notifiable_id' => $notifiable->id ?? null]);
            return null;
        }

        // Payload is FCM v1 format: { message: { token, notification, data } }
        $payload = $notification->toArray($notifiable);
        $response = $this->fcmClient->sendMessage($payload);

        if (ServicesFcmClient::wasSuccessful($response)) {
            Log::info('FCM sent successfully', ['message_id' => $response['name'] ?? null]);
        } else {
            Log::error('FCM send failed', ['response' => $response]);
        }

        return $response;
    }
}
