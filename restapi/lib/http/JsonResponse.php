<?php

namespace lib\http;

class JsonResponse extends Response
{
    protected $data;

    const JSON_ENCODING_OPTION = 15;

    public function __construct( $data = null, $status = 200, $headers = [])
    {
        parent::__construct( '', $status, $headers );

        if ( $data == null ) {
            $data = [];
        }

        is_string( $data ) ? $this->setJson( $data ) : $this->setData( $data );
    }

    public function setData( $data = [] )
    {
        $data = json_encode( $data, self::JSON_ENCODING_OPTION );
        if ( json_last_error() !== \JSON_ERROR_NONE ) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $this->setJson( $data );
    }

    public function setJson( $json )
    {
        if( !$this->isJson($json) ) {
            throw new \InvalidArgumentException("Invalid JSON String");
        }

        $this->data = $json;

        return $this->responseUpdate();
    }

    public function responseUpdate()
    {
        $this->headers->set('Content-Type', sprintf('application/json; charset=%s', $this->charset));

        return $this->setContent( $this->data );
    }

    function isJson( $string )
    {
        return ((is_string($string) &&
                (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }
}