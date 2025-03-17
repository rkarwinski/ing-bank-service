<?php

namespace Src\Utils;

use Exception;
use Src\Utils\Interfaces\LoggerInterface;

class Logger implements LoggerInterface
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = getenv('LOG_FILE_PATH');
    }

    public function log(string $message): void
    {
        $logFilePath = $this->getLogFilePath();
        $messageWithTimestamp = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($logFilePath, $messageWithTimestamp, FILE_APPEND);
    }

    private function getLogFileName(): string
    {
        $date = date('Y-m-d');
        return 'log-' . $date . '.log';
    }

    private function getLogFilePath(): string
    {
        if (empty($this->filePath)) {
            throw new Exception("LOG_FILE PATH not found in ENV");
        }

        if (!is_dir($this->filePath)) {
            mkdir($this->filePath, 0777, true);
        }
        
        $fileName = $this->getLogFileName();

        return $this->filePath . $fileName;
    }
}