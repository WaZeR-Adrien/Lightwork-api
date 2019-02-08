<?php
namespace Kernel\Router;
use Controllers\Controller;
use http\Client\Response;

class Router
{
    /**
     * Current url of the request page
     * @var string
     */
    private $_currentUrl;

    /**
     * All routes
     * @var array
     */
    private $_routes = [];

    /**
     * Name of routes
     * @var array
     */
    private $_namedRoutes = [];

    /**
     * Router constructor.
     * @param string $currentUrl
     */
    public function __construct($currentUrl)
    {
        $this->_currentUrl = $currentUrl;
    }

    /**
     * Create a group of routes
     * @param string $endpoint
     * @param string $callable
     * @param boolean $needToken
     * @param array $needRole
     * @param array $params
     */
    public function group($endpoint, $callable, $needToken = null, $needRole = [], $params = [])
    {
        $group = new Group($endpoint, $needToken, $needRole, $params, $this);

        $callable($group);
    }

    /**
     * @param string $method : GET / POST / PUT / DELETE...
     * @param string $endpoint
     * @param string $callable
     * @param string $name
     * @param boolean $needToken
     * @param array $needRole
     * @param array $params
     * @param array $bodies
     * @return Route
     */
    public function add($method, $endpoint, $callable, $name = null, $needToken = null, $needRole = [], $params = [], $bodies = [])
    {
        $route = new Route($method, $endpoint, $callable, $name, $needToken, $needRole, $params, $bodies);

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
     * Run the router to check if route match with current url
     * @return Route
     * @throws RouterException
     */
    public function run()
    {
        if (!isset($this->_routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException('REQUEST_METHOD doesn\'t exist');
            die();
        }

        try {
            foreach ($this->_routes[$_SERVER['REQUEST_METHOD']] as $route) {

                if ($route->match($this->_currentUrl)) {
                    $res = $route->call($this);

                    if ("object" == gettype($res) && "Kernel\Http\Response" == get_class($res)) {
                        // Set Headers
                        foreach ($res->getHeaders()->getAll() as $key => $value) {
                            header($key . ':' . $value);
                        }
                        
                        // Set Https status code
                        http_response_code($res->getResponseCode()->getStatus());
                        
                        die($res->getBody()->getContent());
                    } else {
                        throw new RouterException('You must return the Response object.', 2);
                    }

                    return $route;
                }

            }
            throw new RouterException('No matching routes', 1);
        }
        catch (RouterException $e) {
            if ($e->getCode() === 1) {
                $res = new \Kernel\Http\Response(null);

                $res->setResponseCode("E_A001");

                die($res->getBody()->getContent());
            }
            else {
                die($e->getMessage());
            }
        }

    }

    /**
     * @param string $name
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

    /**
     * Get all routes for the documentation
     * @return array
     */
    public function getAllRoutes()
    {
        $allRoutes = [];

        foreach ($this->_routes as $method => $routes) {
            foreach ($routes as $route) { $allRoutes[] = $route; }
        }

        return $allRoutes;
    }
}
