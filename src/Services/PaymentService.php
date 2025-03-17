<?php

namespace Src\Services;

use Src\Services\Interfaces\HttpClientInterface;
use Src\Utils\Interfaces\LoggerInterface;
use Exception;
use Src\Services\Interfaces\AuthServiceInterface;
use Ramsey\Uuid\Uuid;
use Src\Utils\Helpers;

class PaymentService {
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private AuthServiceInterface $auth;

    public function __construct(
        HttpClientInterface $httpClient, 
        LoggerInterface $logger,
        AuthServiceInterface $auth,
    )
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->auth = $auth;
    }

    public function createPaymentRequest(float $amount, string $currency, string $description, string $callbackUrl): string {
        try{
            $purchaseId = Uuid::uuid4()->toString();
            $validUntil = (new \DateTime('+1 day'))->format('Y-m-d\TH:i:s\Z');

            $body = [
                'fixedAmount' => [
                    'value' => $amount,
                    'currency' => $currency,
                ],
                'purchaseId' => $purchaseId,
                'validUntil' => $validUntil,
                'description' => $description,
                'returnUrl' => $callbackUrl,
            ];

            $headers = $this->generateHeaders($body);
            
            $response = $this->httpClient->post($this->getUrl(), $body, $headers, true);
            
            return json_decode($response, true)['paymentInitiationUrl'];

        } catch (Exception $e) {
            $this->logger->log("Error creating payment request: " . $e->getMessage());

            throw $e;
        }
    }

    private function getUrl(): string 
    {
        return (
            getenv('BASE_URL') . getenv('PAYMENT_ENDPOINT') 
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

    private function generateHeaders(array $body): array
    {
        $accessToken = $this->auth->getAccessToken();

        $date = gmdate("D, d M Y H:i:s") . " GMT";
        $digest = base64_encode(hash('sha256', json_encode($body), true));

        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'Date' => $date,
            'x-ing-reqid' => Uuid::uuid4()->toString(),
        ];

    }

    # Functions for future implementation of Request Signing 
    private function getSignatureAuthorization(string $digest, string $date): string 
    {
        $request_target = '(request-target): post /payment-requests';
        $date = 'date: ' . $date;
        $digest = 'digest: SHA-256=' . $digest;

        $signingString = "$request_target\n$date\n$digest";
        return $signingString;
    }

    private function signingRequest(string $signingString): string 
    {
        [$signingPem, $signingKey] = Helpers::getCertificateFiles(true);

        $privateKey = openssl_pkey_get_private(file_get_contents($signingKey));

        if ($privateKey === false) {
            $message = 'Failed to load private key: ' .openssl_error_string();
            $this->logger->log($message);
            throw new Exception($message);
        }

        $signature = null;
        $success = openssl_sign($signingString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        
        if (!$success) {
            throw new Exception('String signature failure.');
        }

        return base64_encode($signature);
    }

    public function getAuthorizationHeader(string $digest, string $date): string 
    {
        $signingString = $this->getSignatureAuthorization($digest, $date);
        $signature = $this->signingRequest($signingString); 

        $header = 'Signature keyId="' . $this->getClientId() . '"';
        $header .= ',algorithm="rsa-sha256",headers="(request-target) date digest"';
        $header .= ',signature="' . $signature . '"';

        var_dump($header);

        return $header;
    }
}