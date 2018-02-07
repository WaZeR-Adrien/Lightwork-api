<?php
namespace Kernel\Router;
use Controllers\Error\Error404;

class Router
{
    private $_url;
    private $_routes = [];
    private $_namedRoutes = [];

    /**
     * Router constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->_url = $url;
    }

    /**
     * @param $method : GET / POST / PUT / DELETE
     * @param $path
     * @param $callable
     * @param $name
     * @param $needToken
     * @return Route
     */
    public function add($method, $path, $callable, $name = null, $needToken = null)
    {
        $route = new Route($path, $callable, $needToken);
        $this->_routes[$method][] = $route;
        if (is_string($callable) && is_null($name)) {
            $name = $callable;
        }
        if ($name) {
            $this->_namedRoutes[$name] = $route;
        }
        return $route;
    }

    /**
     * @return mixed
     * @throws RouterException
     */
    public function run()
    {
        if (!isset($this->_routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException('REQUEST_METHOD doesn\'t exist');
        }

        try {
            foreach ($this->_routes[$_SERVER['REQUEST_METHOD']] as $route) {
                if ($route->match($this->_url)) {
                    return $route->call();
                }
            }
            throw new RouterException('No matching routes', 1);
        }
        catch (RouterException $e) {
            if ($e->getCode() === 1) { Error404::index(); }
        }

    }

    /**
     * @param $name
     * @param array $params
     * @return mixed
     * @throws RouterException
     */
    public function url($name, $params = [])
    {
        if (!isset($this->_namedRoutes[$name])) {
            throw new RouterException('No route matches this name');
        }
        return $this->_namedRoutes[$name]->getUrl($params);
    }
}
