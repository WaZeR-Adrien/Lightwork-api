<?php
namespace Kernel\Router;

use Controllers\Error\ErrorToken;
use Kernel\Config;
use Models\Auth;

class Route
{
    private $_path;
    private $_callable;
    private $_matches = [];
    private $_params = [];
    private $_needToken;


    /**
     * Route constructor.
     * @param $path
     * @param $callable
     * @param $needToken
     */
    public function __construct($path, $callable, $needToken)
    {
        $this->_path = trim($path, '/');
        $this->_callable = $callable;
        $this->_needToken = $needToken;
    }

    /**
     * @param $param
     * @param $reg
     * @return $this
     */
    public function with($param, $reg)
    {
        $this->_params[$param] = str_replace('(', '(?:', $reg);
        return $this;
    }

    /**
     * @param $url
     * @return bool
     */
    public function match($url)
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->_path);
        $reg = "#^$path$#i";

        if (!preg_match($reg, $url, $matches)) {
            return false;
        }
        array_shift($matches);

        $this->_matches = $matches;
        return true;
    }

    /**
     * @param $match
     * @return string
     */
    private function paramMatch($match)
    {
        if (isset($this->_params[$match[1]])) {
            return '(' . $this->_params[$match[1]] . ')';
        }
        return '([^/]+)';
    }

    /**
     * @return mixed
     */
    public function call()
    {
        $this->needToken();

        if (is_string($this->_callable)) {
            $params = explode('#', $this->_callable);
            $controller = "Controllers\\$params[0]";
            $controller = new $controller();
            return call_user_func_array([$controller, $params[1]], $this->_matches);
        }
        else {
            return call_user_func_array($this->_callable, $this->_matches);
        }
    }

    /**
     * @param $params
     * @return mixed|string
     */
    public function getUrl($params)
    {
        $path = $this->_path;
        foreach ($params as $k => $v) {
            $path = str_replace(":$k", $v, $path);
        }

        return $path;
    }

    /**
     * Create an error if user does not has a valid token
     */
    public function needToken()
    {
        // If token does not exist : generate error
        if (empty(getallheaders()['X-Auth-Token']) && $this->_needToken === true) {
            ErrorToken::index();
        }
        else {
            $token = Auth::findOne(['token' => getallheaders()['X-Auth-Token']]);

            // If token is invalid : generator error
            // If token expire (set date of expiration in Config file) : generate error
            $expire = Config::getToken()['expire'];

            if (null == $token || (null != $expire && $token->date + $expire < time())) {
                ErrorToken::index();
            }
        }
    }

}
