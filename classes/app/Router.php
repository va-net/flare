<?php

class Router
{
    private static $routes = [];
    private static $pathNotFound = null;
    private static $methodNotAllowed = null;

    /**
     * @return void
     * @param string $expression Route Path
     * @param callback $function Function to Execute
     * @param string $method Request Method
     */
    public static function add($expression, $function, $method = 'get')
    {
        self::$routes[] = [
            'expression' => $expression,
            'function' => $function,
            'method' => $method
        ];
    }

    /**
     * @return void
     * @param string $expression
     * @param string $method
     */
    public static function remove($expression, $method)
    {
        $i = 0;
        foreach (self::$routes as $r) {
            if ($r['expression'] == $expression && $r['method'] == $method) {
                array_splice($routes, $i, 1);
                break;
            }

            $i++;
        }
    }

    /**
     * @return void
     * @param callback $function Path Not Found Function
     */
    public static function pathNotFound($function)
    {
        self::$pathNotFound = $function;
    }

    /**
     * @return void
     * @param callback $function Method Not Allowed Function
     */
    public static function methodNotAllowed($function)
    {
        self::$methodNotAllowed = $function;
    }

    /**
     * @return void
     * @param string $basepath Base Path of the Router
     */
    public static function run($basepath = '/')
    {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);

        if (isset($parsed_url['path'])) {
            $parsed_url['path'] = preg_replace("/\/$/", '', $parsed_url['path']);
            $path = $parsed_url['path'];
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

            $route['expression'] = '^' . $route['expression'];
            $route['expression'] = $route['expression'] . '$';

            if (preg_match('#' . $route['expression'] . '#', $path, $matches)) {
                $path_match_found = true;

                if (strtolower($method) == strtolower($route['method'])) {
                    array_shift($matches);

                    if ($basepath != '' && $basepath != '/') {
                        array_shift($matches);
                    }

                    call_user_func_array($route['function'], $matches);

                    $route_match_found = true;

                    break;
                }
            }
        }

        if (!$route_match_found) {
            if ($path_match_found) {
                header("HTTP/1.0 405 Method Not Allowed");
                if (self::$methodNotAllowed) {
                    call_user_func_array(self::$methodNotAllowed, array($path, $method));
                }
            } else {
                header("HTTP/1.0 404 Not Found");
                if (self::$pathNotFound) {
                    call_user_func_array(self::$pathNotFound, array($path));
                }
            }
        }
    }
}
