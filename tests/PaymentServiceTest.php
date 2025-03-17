<?php

use PHPUnit\Framework\TestCase;
use Src\Services\PaymentService;
use Src\Services\Interfaces\HttpClientInterface;
use Src\Utils\Interfaces\LoggerInterface;
use Src\Services\Interfaces\AuthServiceInterface;

class PaymentServiceTest extends TestCase
{
    private $httpClientMock;
    private $loggerMock;
    private $authMock;
    private $paymentService;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->authMock = $this->createMock(AuthServiceInterface::class);

        $this->paymentService = new PaymentService(
            $this->httpClientMock,
            $this->loggerMock,
            $this->authMock
        );
    }

    public function testCreatePaymentRequestSuccess()
    {
        $amount = 99.95;
        $currency = 'EUR';
        $description = 'Test Payment';
        $callbackUrl = 'https://example.com/callback';

        $paymentInitiationUrl = 'https://www.ing.nl/zakelijk/betaalverzoek/index.html?trxid=xrgq1coyp1pnnfigiaaa6pqn1o9lc0gh';

        $responseBody = json_encode([
            'id' => 'xrgq1coyp1pnnfigiaaa6pqn1o9lc0gh',
            'paymentInitiationUrl' => $paymentInitiationUrl,
        ]);

        $this->httpClientMock->expects($this->once())
            ->method('post')
            ->willReturn($responseBody);

        $result = $this->paymentService->createPaymentRequest($amount, $currency, $description, $callbackUrl);

        $this->assertEquals($paymentInitiationUrl, $result);
    }

    public function testCreatePaymentRequestFailure()
    {
        $amount = 99.95;
        $currency = 'EUR';
        $description = 'Test Payment';
        $callbackUrl = 'https://example.com/callback';

        $errorResponse = json_encode([
            'error' => [
                'severity' => 'critical',
                'code' => 'errParseFailed',
                'message' => 'Unexpected end of file (missing a terminator/close bracket?)',
                'source' => 'system76',
                'target' => 'string',
                'innerErrors' => [
                    [
                        'severity' => 'critical',
                        'code' => 'errParseFailed',
                        'message' => 'Unexpected end of file (missing a terminator/close bracket?)',
                        'source' => 'system76',
                        'target' => 'string'
                    ]
                ]
            ]
        ]);

        $this->httpClientMock->expects($this->once())
            ->method('post')
            ->willThrowException(new Exception("Unexpected end of file (missing a terminator/close bracket?)"));

        $this->loggerMock->expects($this->once())
            ->method('log')
            ->with($this->stringContains('Error creating payment request'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected end of file (missing a terminator/close bracket?)');

        $this->paymentService->createPaymentRequest($amount, $currency, $description, $callbackUrl);
    }
}