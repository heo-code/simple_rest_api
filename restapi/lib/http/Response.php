<?php

namespace lib\http;

class Response
{

    public function __construct(){
    }

    public function json( $data, $status=200 )
    {
        http_response_code( $status );
        header("Content-type: application/json; charset=utf-8");

        echo json_encode( $data );
    }
}