<?php

namespace exception;

use Throwable;
use common\Logger;
use ReflectionClass;

class Handler
{

    protected $logger;

    public function __construct(){
        header("Content-type: application/json; charset=utf-8");
        $this->logger = Logger::getInstance();
    }

    public function renderHttpResponse( $exception )
    {
        $reflect = new ReflectionClass($exception);
        if( $reflect->getShortName() == "ApiException" ) {
            http_response_code( $exception->getResponseCode() );
            echo json_encode( ["message" => $exception->getMessage(), "code" => $exception->getCode()] );

            $this->logger->log( "\n[ERROR]\n[CODE] => " . $exception->getCode() .  "\n[MSG] => " . $exception->getMessage() . "\n[FILE] => " . $exception->getFile() . "\n[LINE] => " . $exception->getLine() . "\n[FUNC] => " . $exception->getFunctionName() );

        } else {
            http_response_code( 400 );
            echo json_encode( ["message" => $exception->getMessage(), "code" => "00" ] );
            $this->logger->log( "\n[ERROR]\n[CODE] => " . $exception->getCode() .  "\n[MSG] => " . $exception->getMessage() . "\n[FILE] => " . $exception->getFile() . "\n[LINE] => " . $exception->getLine() . "\n");
        }
        exit;
    }
}