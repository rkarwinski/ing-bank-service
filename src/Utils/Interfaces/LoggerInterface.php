<?php

namespace Src\Utils\Interfaces;

interface LoggerInterface
{
    public function log(string $message): void;
}