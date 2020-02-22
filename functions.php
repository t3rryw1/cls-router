<?php

use Laura\Lib\Request\Router;

if (!function_exists('register')) {
    /**
     * Function registers a request handler to a method and a uri path,
     * used in routes mappings
     *
     * @param string $method
     * @param string $path
     * @param array $param
     */
    function register($method, $path, ...$param)
    {
        Router::getInstance()->register($method, $path, ...$param);
    }
}
