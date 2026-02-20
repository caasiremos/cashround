<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;


class FcmNotification extends Notification
{
    use Queueable;
    protected $notificationData;


    public function __construct($notificationData)
    {
        $this->notificationData = $notificationData;
    }

     public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $token = $notifiable->routeNotificationFor('fcm');
        $notification = FirebaseNotification::create(
            $this->notificationData['title'],
            $this->notificationData['body']
        );
        // FCM requires all data values to be strings
        $data = $this->notificationData['data'] ?? [];
        $data = array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $data);

        return CloudMessage::new()
            ->withToken($token)
            ->withNotification($notification)
            ->withData($data);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = $this->notificationData['data'] ?? [];
        $data = array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $data);

        return [
            'title' => $this->notificationData['title'],
            'body' => $this->notificationData['body'],
            'data' => $data,
        ];
    }
}
