<?php
/**
 * Kravyo - Custom Pusher Service (Zero-Dependency)
 * Handles Real-Time GPS WebSockets via Pusher REST API.
 */
class PusherService {
    private string $appId;
    private string $key;
    private string $secret;
    private string $cluster;

    public function __construct() {
        require_once APP_PATH . '/../config/pusher.php';
        $this->appId = PUSHER_APP_ID;
        $this->key = PUSHER_KEY;
        $this->secret = PUSHER_SECRET;
        $this->cluster = PUSHER_CLUSTER;
    }

    public function isConfigured(): bool {
        return $this->appId !== 'YOUR_APP_ID' && !empty($this->key);
    }

    public function trigger(string $channel, string $event, array $data): bool {
        if (!$this->isConfigured()) {
            return false;
        }

        $body = json_encode([
            'name' => $event,
            'channels' => [$channel],
            'data' => json_encode($data)
        ]);

        $auth_timestamp = time();
        $auth_version = '1.0';
        $body_md5 = md5($body);

        $path = "/apps/{$this->appId}/events";
        
        $params = [
            'auth_key' => $this->key,
            'auth_timestamp' => $auth_timestamp,
            'auth_version' => $auth_version,
            'body_md5' => $body_md5
        ];

        ksort($params);
        $query_string = http_build_query($params);

        $string_to_sign = "POST\n{$path}\n{$query_string}";
        $auth_signature = hash_hmac('sha256', $string_to_sign, $this->secret);

        $query_string .= "&auth_signature={$auth_signature}";

        $url = "https://api-{$this->cluster}.pusher.com{$path}?{$query_string}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // don't block the request too long

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}
