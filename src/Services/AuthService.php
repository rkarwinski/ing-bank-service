<?php 

namespace Src\Services;

use Src\Services\Interfaces\HttpClientInterface;
use Src\Utils\Interfaces\LoggerInterface;
use Exception;
use Src\Services\Interfaces\AuthServiceInterface;

class AuthService implements AuthServiceInterface
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function getAccessToken(): string
    {
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->getClientId(),
            'scope' => 'greetings:view',
        ];

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        try {
            $response = $this->httpClient->post($this->getUrl(), $body, $headers);
            $data = json_decode($response, true);

            if (!isset($data['access_token'])) {
                throw new Exception("Failed to retrieve access token");
            }

            return $data['access_token'];

        } catch (Exception $e) {
            $this->logger->log("OAuth authentication failed: " . $e->getMessage());
            throw new Exception("Authentication error", 0, $e);
        }
    }

    private function getUrl(): string 
    {
        return (
            getenv('BASE_URL') . getenv('AUTH_ENDPOINT') 
            ?? throw new Exception("URL values not found in ENV")
        );
    }

    private function getClientId(): string 
    {
        return (
            getenv('CLIENT_ID') 
            ?? throw new Exception("ClientID not found in ENV")
        );
    }
}
