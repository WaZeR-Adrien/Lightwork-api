<?php
namespace Kernel\Router;

class Group
{
    private $path;
    private $token;
    private $args;
    private $router;


    /**
     * Group constructor
     * @param $path
     * @param $token
     * @param $args
     * @param Router $router
     */
    public function __construct($path, $token, $args, Router $router)
    {
        $this->path = trim($path, '/');
        $this->token = $token;
        $this->args = null != $args ? $args : [];
        $this->router = $router;
    }

    /**
     * Add new route for this group
     * @param $method
     * @param $path
     * @param $callable
     * @param $token
     * @param array $args
     * @param array $bodies
     */
    public function add($method, $path, $callable, $token = null, $args = [], $bodies = [])
    {
        trim($this->path, '/');
        
        $token = null !== $token ? $token : $this->token;

        $this->_loopIncrement($args);

        $this->router->add($method, ($this->path . $path), $callable, $token, $args, $bodies);
    }

    /**
     * Create a subgroup of the group of routes
     * @param $path
     * @param $callable
     * @param $token
     * @param array $args
     */
    public function group($path, $callable, $token, $args = [])
    {
        trim($this->path, '/');
        
        $token = null !== $token ? $token : $this->token;

        $this->_loopIncrement($args);

        $group = new self(($this->path . $path), $token, $args, $this->router);

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
