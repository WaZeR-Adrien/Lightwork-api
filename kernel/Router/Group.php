<?php
namespace Kernel\Router;
use Kernel\Config;

class Group
{
    private $path;
    private $router;
    private $args = [];
    private $token = null;
    private $routes = [];
    private $groups = [];


    /**
     * Group constructor
     * @param $path
     * @param Router $router
     */
    public function __construct($path, Router $router)
    {
        $this->path = trim($path, '/');
        $this->router = $router;
    }

    /**
     * Add new route for this group
     * @param $method
     * @param $path
     * @param $callable
     * @return Route
     */
    public function add($method, $path, $callable)
    {
        trim($this->path, '/');

        $route = $this->router->add($method, ($this->path . $path), $callable);
        
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Create a subgroup of the group of routes
     * @param $path
     * @param $callable
     * @return Group
     */
    public function group($path, $callable)
    {
        trim($this->path, '/');

        $group = new self(($this->path . $path), $this->router);
        
        $this->groupes[] = $group;

        $callable($group);

        return $group;
    }

    /**
     * @param string $name
     * @param string $type
     * @return Group
     */
    public function arg($name, $type)
    {
        $value = Config::setRegex(ucfirst($type));

        $this->args[$name] = [
            "type" => $type,
            "regex" => str_replace('(', '(?:', $value)
        ];
        
        foreach($this->routes as $route) {
            if (!isset($route->getArgs()[$name])) {
                $route->arg($name, $type);
            }
        }
        
        foreach($this->groups as $group) {
            if (!isset($group->args[$name])) {
                $group->arg($name, $type);
            }
        }

        return $this;
    }

    /**
     * @param bool $required
     * @return Group
     */
    public function token($required = true)
    {
        $this->token = $required;
        
        foreach($this->routes as $route) {
            if ($route->getToken() === null) {
                $route->token($this->token);
            }
        }
        
        foreach($this->groups as $group) {
            if ($group->token === null) {
                $group->token($this->token);
            }
        }

        return $this;
    }
}
