<?php

require_once __DIR__ . '/vendor/autoload.php';

use Src\Services\PaymentService;
use Src\Services\HttpClient;
use Src\Utils\Helpers;
use Src\Utils\Logger;
use Src\Services\AuthService;

Helpers::loadEnv();

$httpClient = new HttpClient();
$logger = new Logger();
$authService = new AuthService($httpClient, $logger);
$paymentService = new PaymentService($httpClient, $logger, $authService);

try {
    $paymentUrl = $paymentService->createPaymentRequest(
        50.00, 
        'EUR', 
        'Order #12345', 
        'https://yourdomain.com/payment/callback'
    );

    echo "Payment request created: " . $paymentUrl . PHP_EOL;
} catch (Exception $e) {
    echo "Error in Payment request: " . $e->getMessage();
}





