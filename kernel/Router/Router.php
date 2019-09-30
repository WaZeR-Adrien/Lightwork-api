<?php
namespace Kernel\Router;
use AdrienM\Collection\Collection;
use AdrienM\Logger\Logger;
use Kernel\Api\ApiErrorCode;
use Kernel\Api\ApiException;
use Kernel\Http\ApiCode;
use Kernel\Loggers\HttpLogger;

class Router
{
    /**
     * Current url of the request page
     * @var string
     */
    private $currentUrl;

    /**
     * All routes
     * @var array
     */
    private $routes = [];

    /**
     * Router constructor.
     * @param string $currentUrl
     */
    public function __construct($currentUrl)
    {
        $this->currentUrl = $currentUrl;
    }

    /**
     * Create a group of routes
     * @param string $path
     * @param callable $callable
     * @return Group
     */
    public function group($path, $callable)
    {
        $group = new Group($path, $this);

        $callable($group);

        return $group;
    }

    /**
     * @param string $method : GET / POST / PUT / DELETE...
     * @param string $path
     * @param callable $callable
     * @return Route
     */
    public function add($method, $path, $callable)
    {
        $route = new Route($method, $path, $callable);

        $this->routes[$method][] = $route;

        return $route;
    }

    /**
     * Run the router to check if route match with current url
     * @return Route
     * @throws RouterException
     */
    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$method])) {
            throw new RouterException('REQUEST_METHOD doesn\'t exist');
            die();
        }

        // Instance of HttpLogger to log errors and success called
        $httpLogger = HttpLogger::getInstance();
        try {
            foreach ($this->routes[$method] as $route) {

                if ($route->match($this->currentUrl)) {
                    $res = $route->call($this);


                    if ("object" == gettype($res) && "Kernel\Http\Response" == get_class($res)) {
                        // Log the success
                        $type = is_string($route->getCallable()) ? $route->getCallable() : "FUNCTION";
                        $httpLogger->setType($type);
                        $httpLogger->write("SUCCESS, , " . $res->getStatus() . ", $method, " . $this->currentUrl . ", " . $_SERVER['REMOTE_ADDR']);

                        // Set Headers
                        foreach ($res->getHeaders()->getAll() as $key => $value) {
                            header($key . ':' . $value);
                        }

                        // Set Https status code
                        http_response_code($res->getStatus());

                        die($res->getContent());
                    } else {
                        throw new RouterException('You must return an instance of Response.', 1);
                    }

                    return $route;
                }

            }
            throw new ApiException(ApiErrorCode::NF404);

        } catch (\Exception $e) {
            // Instance of Logger to log exception
            $logger = Logger::getInstance();
            // Log the exception
            $logger->setType(get_class($e));
            $logger->write("[" . $e->getCode() . "] " . $e->getMessage());

            if ($logger->getType()  == "Kernel\Api\ApiException") {
                $httpLogger->setType( $logger->getType() );
                $httpLogger->write($e->getCode() . ", , " . $e->getStatus() . ", $method, " . $this->currentUrl . ", " . $_SERVER['REMOTE_ADDR']);
            }

            $res = new \Kernel\Http\Response();

            $res->setBody(Collection::from([
                "error" => [
                    "code" => $e->getCode(),
                    "message" => $e->getMessage()
                ]
            ]));

            $res->toJson();

            // Set Content Type
            header("Content-Type:" . $res->getHeaders()->get("Content-Type"));

            // Set Https status code
            $status = ($logger->getType() == "Kernel\Api\ApiException") ? $e->getStatus() : 500;
            http_response_code($status);

            die($res->getContent());
        }

    }

    /**
     * Get all routes for the documentation
     * @return array
     */
    public function getAllRoutes()
    {
        $allRoutes = [];

        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route) { $allRoutes[] = $route; }
        }

        return $allRoutes;
    }
}
