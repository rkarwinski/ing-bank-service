
<?php

use PHPUnit\Framework\TestCase;
use Src\Services\AuthService;
use Src\Services\Interfaces\HttpClientInterface;
use Src\Utils\Interfaces\LoggerInterface;
use Exception;

class AuthServiceTest extends TestCase
{
    private HttpClientInterface $httpClientMock;
    private LoggerInterface $loggerMock;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->authService = new AuthService($this->httpClientMock, $this->loggerMock);
    }

    public function testGetAccessTokenSuccess(): void
    {
        $response = json_encode([
            "access_token" => "eyJhbGciOiJkaXIiLCJlbmMiOi....",
            "expires_in" => 900,
            "scope" => "scope1 scope2",
            "token_type" => "Bearer",
            "refresh_token" => "eyJhbGciOiJkaXIiLCJlbmMiOi....",
            "refresh_token_expires_in" => 3600,
            "client_id" => "dc46e4b8-70aa-4da4-b74a-a2576680f004",
            "keys" => [
                [
                    "kty" => "RSA",
                    "alg" => "RS256",
                    "use" => "sig",
                    "kid" => "string",
                    "n" => "string",
                    "e" => "string",
                    "x5t" => "string",
                    "x5c" => ["string"]
                ]
            ]
        ]);

        $this->httpClientMock->method('post')
            ->willReturn($response);

        $accessToken = $this->authService->getAccessToken();

        $this->assertEquals("eyJhbGciOiJkaXIiLCJlbmMiOi....", $accessToken);
    }

    public function testGetAccessTokenFailure(): void
    {
        $response = json_encode([
            "message" => "error token"
        ]);

        $this->httpClientMock->method('post')
            ->willReturn($response);

        $this->loggerMock->expects($this->once())
            ->method('log')
            ->with($this->stringContains('OAuth authentication failed'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Authentication error');

        $this->authService->getAccessToken();
    }
}