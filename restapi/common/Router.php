<?php

namespace common;

use lib\http\Request;
use exception\ApiException;

class Router
{
    private static $routes = array();

    public function __construct()
    {
    }

    public static function add($method, $expression,  $function)
    {
        array_push(self::$routes, array(
            'expression' => $expression,
            'function' => $function,
            'method' => $method
        ));
    }

    public static function run($basepath = '')
    {
        // Parse current url
        $basepath = "/" . $basepath;
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);

        if (isset($parsed_url['path'])) {
            $path = $parsed_url['path'];
            if (substr($path, -1) != "/") {
                $path = $path . "/";
            }
        } else {
            $path = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        $path_match_found = false;

        $route_match_found = false;

        foreach (self::$routes as $route) {

            if ($basepath != '' && $basepath != '/') {
                $route['expression'] = '(' . $basepath . ')' . $route['expression'];
            }

            // Add 'find string start' automatically
            $route['expression'] = '^' . $route['expression'];

            // Add 'find string end' automatically
            $route['expression'] = $route['expression'] . '/$';

            // Check path match
            if (preg_match('#' . $route['expression'] . '#', $path, $matches)) {
                $path_match_found = true;

                if (strtoupper($method) == "OPTIONS") {
                    self::corsOption($route['method'], $method);
                    $route_match_found = true;
                } else {
                    // Check method match
                    if (strtolower($method) == strtolower($route['method'])) {

                        array_shift($matches); // Always remove first element. This contains the whole string

                        if ($basepath != '' && $basepath != '/') {
                            array_shift($matches); // Remove basepath
                        }

                        list($classPath, $function) = explode("@", $route['function']);
                        $classObj = new $classPath();
                        call_user_func([$classObj, $function], new Request());

                        self::corsOption($route['method'], $method);
                        $route_match_found = true;
                    }
                }
                // Do not check other routes
                break;
            }
        }

        // No matching route was found
        if (!$route_match_found) {
            // But a matching path exists
            if ($path_match_found) {
                throw new ApiException("Fail Method Not Allowed", "-405", 405);
            } else {
                throw new ApiException("Fail Is Not Match Path", "-404",  401);
            }
            exit();
        }
    }

    private static function corsOption($allowMethod, $routeMethod)
    {
        $cors = getConfig("cors");
        if (isset($cors['allow_origin'])) {
            header("Access-Control-Allow-Origin:" . implode(", ", $cors['allow_origin']));
        }
        if (strtoupper($routeMethod) == "OPTIONS") {
            if (isset($cors['supports_credentials']) && ($cors['supports_credentials'] === true)) {
                header("Access-Control-Allow-Credentials: true");
            }
            if (isset($cors['supports_credentials'])) {
                header("Access-Control-Allow-Methods:" . implode(", ", $cors['allow_method']));
            }
            if (isset($cors['max_age']) && is_numeric($cors['max_age']) && $cors['max_age'] > 0) {
                header("Access-Control-Max-Age:" . $cors['max_age']);
            }
            header("Allow: " . strtoupper($allowMethod));
        }
    }
}
