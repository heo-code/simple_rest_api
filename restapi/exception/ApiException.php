<?php

namespace exception;

use Exception;

class ApiException extends Exception
{
    private $nErrResponseCode;

    public function __construct( $sMsg='', $code='', $nResponseCode=400 )
    {
        $this->nErrResponseCode = $nResponseCode;
        if( !strlen( trim( $sMsg ) ) ) {
            $sMsg = "Fail Unknown Error";
        }
        parent::__construct( $sMsg, $code);
    }

    public function getResponseCode()
    {
        return $this->nErrResponseCode;
    }

    public function getFunctionName()
    {
        $aExceptTrace = $this->getTrace();
        if( isset( $aExceptTrace[0]['function'] ) ) {
            return $aExceptTrace[0]['function'];
        }
        return "";
    }
}