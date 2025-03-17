<?php

namespace Src\Utils;

use Exception;

class Helpers {
    public static function loadEnv(): void
    {
        $env = file_get_contents(".env");
        $lines = explode("\n",$env);

        foreach($lines as $line){
            preg_match("/([^#]+)\=(.*)/",$line,$matches);
            if(isset($matches[2])){ 
                putenv(trim($line)); 
            }
        }
    }

    public static function getCertificateFiles(bool $isSigning = false)
    {
        $type = ($isSigning === true) ? "signing" : "tls";
        
        $pemPath = getenv('CERTS_FILE_PATH') . "example_client_{$type}.pem";
        $keyPath = getenv('CERTS_FILE_PATH') . "example_client_{$type}.key";

        if (empty($pemPath) || empty($keyPath)) {
            throw new Exception("certificates not found.");
        }

        return [$pemPath, $keyPath];
    }
}

