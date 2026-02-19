<?php
namespace App\Services\Firebase\Services;
use Google\Client as GoogleClient;
use GuzzleHttp\Client as HttpClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmClient
{
    private $googleClient;
    private $httpClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
        $this->googleClient->setAuthConfig(storage_path('app/firebase/firebase_credentials.json'));
        $this->googleClient->addScope('https://fcm.googleapis.com/auth/firebase.messaging');
        $this->httpClient = new HttpClient();
    }

    public function sendMessage($token, $notification)
    {
        // Fetch the OAuth 2.0 access token
        try {
            $tokenResponse = $this->googleClient->fetchAccessTokenWithAssertion();
            \Log::info('Access Token Response:', $tokenResponse);
            $accessToken = $tokenResponse['access_token'] ?? null;
            if (!$accessToken) {
                \Log::error('Failed to retrieve access token', $tokenResponse);
                throw new \Exception('Failed to retrieve access token');
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching access token: ' . $e->getMessage());
            return ['error' => 'Could not fetch access token'];
        }
        // Define the Firebase Cloud Messaging API v1 URL
        $fcmUrl = 'https://fcm.googleapis.com/v1/projects/newflutterpushnotifications/messages:send';
        // Prepare the payload
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $notification['title'],
                    'body'  => $notification['body'],
                ],
                'data' => $notification['data'] ?? [],
            ],
        ];
        try {
            // Make the HTTP request to FCM API
            $response = $this->httpClient->post($fcmUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            \Log::error('Error sending FCM message: ' . $e->getMessage());
            return ['error' => 'Failed to send message'];
        }
    }

    /**
     * Check if the FCM sendMessage response indicates success.
     * Success: response contains top-level "name" (e.g. projects/.../messages/...).
     * Failure: response contains "error" or is missing "name".
     */
    public static function wasSuccessful($response): bool
    {
        if (! is_array($response)) {
            return false;
        }
        if (isset($response['error'])) {
            return false;
        }
        return isset($response['name']) && is_string($response['name']);
    }
}