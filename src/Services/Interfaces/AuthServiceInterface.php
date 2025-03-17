<?php 

namespace Src\Services\Interfaces;

interface AuthServiceInterface {
    public function getAccessToken(): string;
}