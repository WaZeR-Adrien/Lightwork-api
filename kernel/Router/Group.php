<?php
namespace Kernel\Router;

class Group
{
    private $_endpoint;
    private $_needToken;
    private $_needRole;
    private $_params = [];
    private $_router;


    /**
     * Group constructor
     * @param $endpoint
     * @param $needToken
     * @param $needRole
     * @param $params
     * @param Router $router
     */
    public function __construct($endpoint, $needToken, $needRole, $params, Router $router)
    {
        $this->_endpoint = trim($endpoint, '/');
        $this->_needToken = $needToken;
        $this->_needRole = $needRole;
        $this->_params = null != $params ? $params : [];
        $this->_router = $router;
    }

    /**
     * Add new route for this group
     * @param $method
     * @param $endpoint
     * @param $callable
     * @param null $name
     * @param null $needToken
     * @param array $needRole
     * @param array $params
     * @param array $bodies
     */
    public function add($method, $endpoint, $callable, $name = null, $needToken = null, $needRole = [], $params = [], $bodies = [])
    {
        trim($this->_endpoint, '/');
        $needToken = null !== $needToken ? $needToken : $this->_needToken;

        $this->_loopIncrement($params, $needRole);

        $this->_router->add($method, ($this->_endpoint . $endpoint), $callable, $name, $needToken, $needRole, $params, $bodies);
    }

    /**
     * Create a subgroup of the group of routes
     * @param $endpoint
     * @param $callable
     * @param null $needToken
     * @param array $needRole
     * @param array $params
     */
    public function group($endpoint, $callable, $needToken = null, $needRole = [], $params = [])
    {
        trim($this->_endpoint, '/');
        $needToken = null !== $needToken ? $needToken : $this->_needToken;

        $this->_loopIncrement($params, $needRole);

        $group = new self(($this->_endpoint . $endpoint), $needToken, $needRole, $params, $this->_router);

        $callable($group);
    }

    /**
     * Increment the $this->_params and $this->_needRole array with the new values
     * Overwrite old values if they have the same key
     * @param array $params
     * @param array $needRole
     */
    private function _loopIncrement(&$params = [], &$needRole = [])
    {
        $newParams = $this->_params;
        foreach ($params as $k => $v) {
            $newParams[$k] = $v;
        }
        $params = $newParams;

        $newRoles = $this->_needRole;
        foreach ($needRole as $k => $v) {
            $newRoles[$k] = $v;
        }
        $needRole = $newRoles;
    }
}