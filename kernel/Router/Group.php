<?php
namespace Kernel\Router;

class Group
{
    private $path;
    private $args;
    private $router;


    /**
     * Group constructor
     * @param $path
     * @param $token
     * @param $args
     * @param Router $router
     */
    public function __construct($path, $args, Router $router)
    {
        $this->path = trim($path, '/');
        $this->args = null != $args ? $args : [];
        $this->router = $router;
    }

    /**
     * Add new route for this group
     * @param $method
     * @param $path
     * @param $callable
     * @param array $args
     * @param array $bodies
     */
    public function add($method, $path, $callable, $args = [], $bodies = [])
    {
        trim($this->path, '/');

        $this->_loopIncrement($args);

        $this->router->add($method, ($this->path . $path), $callable, $args, $bodies);
    }

    /**
     * Create a subgroup of the group of routes
     * @param $path
     * @param $callable
     * @param array $args
     */
    public function group($path, $callable, $args = [])
    {
        trim($this->path, '/');

        $this->_loopIncrement($args);

        $group = new self(($this->path . $path), $args, $this->router);

        $callable($group);
    }

    /**
     * Increment the $this->_params array with the new values
     * Overwrite old values if they have the same key
     * @param array $args
     */
    private function _loopIncrement(&$args = [])
    {
        $newArgs = $this->args;
        foreach ($args as $k => $v) {
            $newArgs[$k] = $v;
        }
        $args = $newArgs;
    }
}
