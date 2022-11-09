<?php

namespace controller\test;

use common\Logger;
use model\ApiAccessToken;
use exception\ApiException;
use lib\traits\CommonTrait;

class TestController
{

    use CommonTrait;

    protected $modelToken;

    public function __construct()
    {
        $this->modelToken = new ApiAccessToken();
    }

    public function rest_api($request)
    {
        $data = ["result" => "SUCCESS", "msg" => "hello world"];
        Logger::log( $data );

        return json_response( $data );
    }

    public function rest_api_exception($request)
    {
        throw new ApiException("api_exception", 01);
    }

    public function test_makeToken($request)
    {
        $sToken = $this->tokenRandom(30);
        $this->modelToken->createToken("toekn_name", $sToken, ["abilities"], '11.22.33.44', '2022-10-13');
    }

    public function tokenRandom($length = 20)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = openssl_random_pseudo_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}
