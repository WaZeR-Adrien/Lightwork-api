<?php
namespace Kernel\Router;

use Controllers\Controller;
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
     * Route constructor
     * @param $path
     * @param $callable
     * @param $needToken
     * @param $params
     */
    public function __construct($path, $callable, $needToken, $params)
    {
        $this->_path = trim($path, '/');
        $this->_callable = $callable;
        $this->_needToken = $needToken;

        $this->_params($params);
    }

    /**
     * @param $params
     * @return $this
     */
    private function _params($params)
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $this->_params[$key] = str_replace('(', '(?:', $value);
            }
        }
    }

    /**
     * @param $url
     * @return bool
     */
    public function match($url)
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, '_paramMatch'], $this->_path);
        $reg = "#^$path$#i";

        // Get values
        if (!preg_match($reg, $url, $matchesValues)) {
            return false;
        }
        array_shift($matchesValues);

        // Get keys
        preg_match_all('#:([\w]+)#', $this->_path, $matchesKeys);

        $matchesKeys = $matchesKeys[1];
        $matches = [];

        // Match key with value
        for ($i = 0; $i < count($matchesKeys); $i++) {
            $matches[$matchesKeys[$i]] = $matchesValues[$i];
        }
        $this->_matches = $matches;
        return true;
    }

    /**
     * @param $match
     * @return string
     */
    private function _paramMatch($match)
    {
        if (isset($this->_params[$match[1]])) {
            $reg = '(' . $this->_params[$match[1]] . ')';
            return $reg;
        }
        $reg = '([^/]+)';
        return $reg;
    }

    /**
     * @return mixed
     */
    public function call()
    {
        $this->needToken();

        $data = $this->_setDataFromMethod((object) $this->_matches);

        if (is_string($this->_callable)) {
            $params = explode('#', $this->_callable);
            $controller = "Controllers\\$params[0]";
            $controller = new $controller();

            return call_user_func_array([$controller, $params[1]], $data);
        }
        else {
            return call_user_func_array($this->_callable, $data);
        }
    }

    /**
     * Return value sent with URL (matches) and in POST or PUT
     * @return array of objects
     */
    private function _setDataFromMethod($matches)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if (in_array($method, ['PUT', 'PATCH'])) {
            return [Controller::parse_http_put(), $matches];
        } else if ($method === 'POST') {
            return [(object) $_POST, $matches];
        } else {
            return [$matches];
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
        if ($this->_needToken === true) {
            // If token does not exist : generate error
            if (empty(Controller::getHeader('X-Auth-Token'))) {
                ErrorToken::index();
            } else {
                if (explode('/', Controller::getHeader('X-Auth-Token'))[1] != sha1($_SERVER['REMOTE_ADDR'])) {
                    // If the second part of token (the IP) is not equal to the IP of user which has sent the request : generator error
                    ErrorToken::index();
                }

                $token = Auth::findOne(['token' => Controller::getHeader('X-Auth-Token')]);

                // If token is invalid : generator error
                // If token expire (set date of expiration in Config file) : generate error
                $expire = Config::getToken()['expire'];

                if (null == $token || (null != $expire && $token->date + $expire < time())) {
                    ErrorToken::index();
                }
            }
        }
    }

}
