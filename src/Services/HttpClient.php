<?php

namespace Src\Services;

use Src\Services\Interfaces\HttpClientInterface;
use GuzzleHttp\Client;
use Src\Utils\Helpers;

class HttpClient implements HttpClientInterface {
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function get(string $url, array $headers = []): string
    {
        $response = $this->client->request('GET', $url, ['headers' => $headers]);
        return $response->getBody()->getContents();
    }

    public function post(string $url, array $data = [], array $headers = [], bool $isJson = false): string
    {
        [$tlsCert, $tlsKey] = Helpers::getCertificateFiles();

        $type = ($isJson) ? 'json' : 'form_params';

        $response = $this->client->request('POST', $url, [
            'headers' => $headers,
            $type => $data,
            'cert' => $tlsCert,
            'ssl_key' => $tlsKey,
        ]);
        return $response->getBody()->getContents();
    }
}
