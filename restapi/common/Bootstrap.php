<?php

namespace common;

use Throwable;
use exception\Handler as ExceptionHandler;
use exception\ApiException;

class Bootstrap 
{

    protected $ExceptionHandler;

    public function __construct(){
        $this->ExceptionHandler = new ExceptionHandler();
    }

    public function registers()
    {
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
        ini_set('display_errors', 'Off');
    }

    public function handleException( $e )
    {
        $this->ExceptionHandler->renderHttpResponse( $e );
    }

    public function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            // system error
            $this->handleException( new ApiException( $error['message'], '-500', 404 ) );
        }
    }

    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}