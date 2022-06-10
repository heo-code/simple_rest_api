<?php

use lib\http\Request;
use lib\traits\CommonTrait;

class Unit
{

    use CommonTrait;

    /**
     * Vaildate Request
     */
    public function test_vaildateRequestParams()
    {
        $request = new Request();
        $request->set("param1", 1234);
        $request->set("param2", "test_test");
        $request->set("param3", "A");
        $request->set("param4", "test@email.com");
        $request->set("param5", "abcdEFG");

        $validate = [
            'param1' => 'nullable|numeric',
            "param2" => 'required|string|max:10',
            "param3" => 'required|string|size:1',
            "param4" => "nullable|email",
            'param5' => ["nullable", "reg" => "([a-zA-Z]+)"]
        ];

        $this->checkRequestParams($request, $validate);
    }

    public function test_makeToken()
    {
        $sToken = $this->tokenRandom(20);
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
