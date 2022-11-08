<?php

use common\Env;
use lib\http\Response;

/**
 * helper
 */

if (!function_exists('env')) {
    function env($value, $default = null)
    {
        $env = new Env();
        $dotEnv = $env->getDotEnv();

        return isset($dotEnv[strtoupper($value)]) ? $dotEnv[strtoupper($value)] : $default;
    }
}

if (!function_exists('getConfig')) {
    function getConfig($type="")
    {
        static $config;

        if( !is_array( $config ) ) {
            $config = include_once( __DIR__ . "/./Config.php" );
        }

        if( isset($config) && is_array($config) ) {
            if( $type && isset( $config[ strtolower($type) ] ) ){
                return $config[ strtolower($type) ];
            }
            return $config;
        }
        return false;
    }
}

if (!function_exists('json_response')) {
    function json_response($data = null, $status = 200, $headers = [])
    {
        $jsonResponse = new JsonResponse($data, $status, $headers);
        return $jsonResponse->send();
    }
}

/**
 * php 7 function
 */

if (!function_exists('mb_chr')) {
    function mb_chr($ord, $encoding = 'UTF-8')
    {
        if ($encoding === 'UCS-4BE') {
            return pack("N", $ord);
        } else {
            return mb_convert_encoding(mb_chr($ord, 'UCS-4BE'), $encoding, 'UCS-4BE');
        }
    }
}

if (!function_exists('mb_ord')) {
    function mb_ord($char, $encoding = 'UTF-8')
    {
        if ($encoding === 'UCS-4BE') {
            list(, $ord) = (strlen($char) === 4) ? @unpack('N', $char) : @unpack('n', $char);
            return $ord;
        } else {
            return mb_ord(mb_convert_encoding($char, 'UCS-4BE', $encoding), 'UCS-4BE');
        }
    }
}

if (!function_exists('mb_htmlentities')) {
    function mb_htmlentities($string, $hex = true, $encoding = 'UTF-8')
    {
        return preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($match) use ($hex) {
            return sprintf($hex ? '&#x%X;' : '&#%d;', mb_ord($match[0]));
        }, $string);
    }
}

if (!function_exists('mb_html_entity_decode')) {
    function mb_html_entity_decode($string, $flags = null, $encoding = 'UTF-8')
    {
        return html_entity_decode($string, ($flags === NULL) ? ENT_COMPAT | ENT_HTML401 : $flags, $encoding);
    }
}
