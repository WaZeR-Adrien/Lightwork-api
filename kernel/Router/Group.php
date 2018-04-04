<?php
namespace Kernel\Router;

class Group
{
    private $_path;
    private $_needToken;
    private $_params;
    private $_router;


    /**
     * Group constructor
     * @param $path
     * @param $needToken
     * @param $params
     * @param Router $router
     */
    public function __construct($path, $needToken, $params, Router $router)
    {
        $this->_path = trim($path, '/');
        $this->_needToken = $needToken;
        $this->_params = $params;
        $this->_router = $router;
    }

    /**
     * Add new route for this group
     * @param $method
     * @param $path
     * @param $callable
     * @param null $name
     * @param null $needToken
     * @param null $params
     */
    public function add($method, $path, $callable, $name = null, $needToken = null, $params = null)
    {
        trim($this->_path, '/');
        $needToken = null !== $needToken ? $needToken : $this->_needToken;

        foreach ($this->_params as $key => $value) {
            $params[$key] = $value;
        }

        $this->_router->add($method, $this->_path.$path, $callable, $name, $needToken, $params);
    }
}
