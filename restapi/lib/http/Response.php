<?php

namespace lib\http;

class Response
{
    public $headers;

    protected $content;

    protected $statusCode;

    protected $charset;

    public function __construct( $content = '', $status = 200, $headers = [] )
    {
        $this->headers = new ResponseHeaderBag( $headers );
        $this->setContent( $content );
        $this->setStatusCode( $status );
        $this->charset = "utf-8";
    }

    public function sendHeaders()
    {
        if ( headers_sent() ) {
            return $this;
        }

        // headers
        http_response_code( $this->statusCode );
        foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $replace = 0 === strcasecmp($name, 'Content-Type');
            foreach ($values as $value) {
                header($name.': '.$value, $replace, $this->statusCode);
            }
        }

        return $this;
    }

    public function setStatusCode( $statusCode )
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function setContent( $content )
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function sendContent()
    {
        echo $this->content;
        return $this;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        static::closeOutputBuffers( 0, true );

        return $this;
    }

    public static function closeOutputBuffers( $targetLevel=0, $flush=true )
    {
        $status = ob_get_status(true);
        $level = \count($status);
        $flags = \PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }
}