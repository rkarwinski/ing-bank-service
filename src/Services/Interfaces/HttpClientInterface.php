<?php 

namespace Src\Services\Interfaces;

interface HttpClientInterface {
    public function get(string $url, array $headers = []): string;
    public function post(string $url, array $data = [], array $headers = []): string;
}