<?php

namespace common;

class Env
{
    protected static $env;

    public function __construct()
    {
    }

    public function getDotEnv()
    {
        if (is_object(self::$env)) {
            return self::$env;
        }

        $env = parse_ini_file(__DIR__ . "/../.env");
        return $env;
    }
}
