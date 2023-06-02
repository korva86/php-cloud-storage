<?php


namespace wfm;


class Router
{

    protected static array $routes = [];
    protected static array $route = [];
    protected static string $method = "GET";

    public static function add($regexp, $route = [], $method = "GET")
    {
        self::$routes[$regexp] = $route;
        self::$method = $method;
//        debug(self::$method, );
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function getRoute(): array
    {
        return self::$route;
    }

    protected static function removeQueryString($url)
    {
        if ($url) {
            $params = explode('&', $url, 2);
            if (false === str_contains($params[0], '=')) {
                return rtrim($params[0], '/');
            }
        }
        return '';
    }

    public static function dispatch($url)
    {
        $url = self::removeQueryString($url);
        if (self::matchRoute($url)) {
            $controller = 'app\controllers\\' . self::$route['admin_prefix'] . self::$route['controller'] . 'Controller';
            if (class_exists($controller)) {

                /** @var Controller $controllerObject */
                $controllerObject = new $controller(self::$route);

                $controllerObject->getModel();
//debug(self::$method);
                $method = $_SERVER["REQUEST_METHOD"];
                self::$method = $method;
                $action = self::lowerCamelCase(self::$route['action'] . 'Action');
                if (method_exists($controllerObject, $action) && $method === strtoupper(self::$method)) {
                    $controllerObject->$action();
//                    $controllerObject->getView();
                } else {
                    throw new \Exception("Метод {$method} {$controller}::{$action} не найден", 404);
                }
            } else {
                throw new \Exception("Контроллер {$controller} не найден", 404);
            }

        } else {
            throw new \Exception("Страница не найдена", 404);
        }
    }

    public static function matchRoute($url): bool
    {
        debug(self::$method);
        foreach (self::$routes as $pattern => $route) {
//            if ($_SERVER["REQUEST_METHOD"] !== self::$method) {
//                echo 'FALSE';
//                return false;
//            }
            if (preg_match("#{$pattern}#", $url, $matches)) {

                foreach ($matches as $k => $v) {
                    if (is_string($k)) {
                        $route[$k] = $v;
                    }
                }
                if (empty($route['action'])) {
                    $route['action'] = 'index';
                }
                if (!isset($route['admin_prefix'])) {
                    $route['admin_prefix'] = '';
                } else {
                    $route['admin_prefix'] .= '\\';
                }
                $route['controller'] = self::upperCamelCase($route['controller']);
                self::$route = $route;
                return true;
            }
        }
        return false;
    }

    // CamelCase
    protected static function upperCamelCase($name): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
    }

    // camelCase
    protected static function lowerCamelCase($name): string
    {
        return lcfirst(self::upperCamelCase($name));
    }

}