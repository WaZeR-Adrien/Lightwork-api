<?php
namespace Kernel\Router;
use Controllers\Controller;
use http\Client\Response;
use Kernel\Http\ApiCode;

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
     * @param callable $callable
     * @param boolean $token
     * @param array $args
     */
    public function group($endpoint, $callable, $token = null, $args = [])
    {
        $group = new Group($endpoint, $args, $token, $this);

        $callable($group);
    }

    /**
     * @param string $method : GET / POST / PUT / DELETE...
     * @param string $endpoint
     * @param callable $callable
     * @param boolean $token
     * @param array $args
     * @param array $bodies
     * @return Route
     */
    public function add($method, $endpoint, $callable, $token = null, $args = [], $bodies = [])
    {
        $route = new Route($method, $endpoint, $callable, $token, $args, $bodies);

        $this->_routes[$method][] = $route;

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
                        http_response_code($res->getStatus());

                        die($res->getContent());
                    } else {
                        throw new RouterException('You must return the Response object.', 2);
                    }

                    return $route;
                }

            }
            throw new RouterException('No matching routes', 1);
        }
        catch (RouterException $e) {
            // Error 404
            if ($e->getCode() === 1) {
                $res = new \Kernel\Http\Response();

                $res->fromApi("E_A001")->toJson();

                // Set Content Type
                header("Content-Type:" . $res->getHeaders()->get("Content-Type"));

                // Set Https status code
                http_response_code($res->getApiCode()->getStatus());

                die($res->getContent());
            }
            else {
                die($e->getMessage());
            }
        }

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
