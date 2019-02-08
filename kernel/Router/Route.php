<?php
namespace Kernel\Router;

use Controllers\Controller;
use Kernel\Config;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Utils;
use Models\Auth;
use Models\User;

class Route
{
    /**
     * Method
     * @var string
     */
    private $_method;

    /**
     * Endpoint
     * @var string
     */
    private $_endpoint;

    /**
     * Method / function to call
     * Ex: MyController#myMethod
     * @var string
     */
    private $_callable;

    /**
     * Matches params
     * @var array
     */
    private $_matches = [];

    /**
     * Type of params
     * Ex: ['id' => 'Int']
     * @var array
     */
    private $_typeParams = [];

    /**
     * Params
     * @var array
     */
    private $_params = [];

    /**
     * Bodies
     * @var array
     */
    private $_bodies = [];

    /**
     * Name / Description for the documentation
     * @var string
     */
    private $_name;

    /**
     * Need token
     * @var boolean
     */
    private $_needToken;

    /**
     * Need role
     * Ex N°1: ['>' => 2]
     * Role superior to 2
     *
     * Ex N°2: ['>' => 1, '<=' => 4]
     * Role superior to 1 and superior or equal to 4
     *
     * Ex N°3: ['=' => 3]
     * Role equal to 3
     * @var array
     */
    private $_needRole = [];


    /**
     * Route constructor
     * @param $method
     * @param $endpoint
     * @param $callable
     * @param $name
     * @param $needToken
     * @param $needRole
     * @param $params
     * @param $bodies
     */
    public function __construct($method, $endpoint, $callable, $name, $needToken, $needRole, $params, $bodies)
    {
        $this->_method = $method;
        $this->_endpoint = trim($endpoint, '/');
        $this->_callable = $callable;
        $this->_name = $name;
        $this->_needToken = $needToken;
        $this->_needRole = $needRole;
        $this->_bodies = $bodies;

        $this->_params($params);
    }

    /**
     * Set params
     * @param $params
     */
    private function _params($params)
    {
        if (!empty($params)) {
            foreach ($params as $key => $value) {

                $value = Config::setRegex(ucfirst($value));

                $this->_params[$key] = str_replace('(', '(?:', $value);
            }
        }

        // Set the type of params (use for the doc)
        preg_match_all('#:([\w]+)#', $this->_endpoint, $urlParams);

        foreach ($urlParams[1] as $param) {
            if (array_key_exists($param, $params)) {
                $this->_typeParams[$param] = $params[$param];
            } else {
                $this->_typeParams[$param] = 'String';
            }
        }
    }

    /**
     * Check if currentUrl match with the url of this route
     * @param $currentUrl
     * @return bool
     */
    public function match($currentUrl)
    {
        $currentUrl = trim($currentUrl, '/');
        $endpoint = preg_replace_callback('#:([\w]+)#', [$this, '_paramMatch'], $this->_endpoint);
        $reg = "#^$endpoint$#i";

        // Get values
        if (!preg_match($reg, $currentUrl, $matchesValues)) {
            return false;
        }
        array_shift($matchesValues);

        // Get keys
        preg_match_all('#:([\w]+)#', $this->_endpoint, $matchesKeys);

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
     * Call the method of the route
     * @param Router $router
     * @return mixed
     */
    public function call(Router $router)
    {
        $request = new Request($_SERVER['REQUEST_METHOD']);
        $this->_setRequestData($request);

        $response = new Response($this);

        // If there are errors with token : generate error
        if (!empty($this->needToken()["error"])) {
            $response->setResponseCode($this->needToken()["error"]);
            return $response;
        }

        // If there are errors with data bodies : generate error
        if (!empty($this->checkBodies()["error"])) {
            $response->setResponseCode($this->checkBodies()["error"]);
            return $response;
        }

        $token = Utils::getHeader('X-Auth-Token');
        if (!empty($token)) {
            $request->setToken($token);
        }

        // Request - Response - Routes
        $data = [$request, $response, $router->getAllRoutes()];

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
     * Set data
     * @param Request $request
     * @return Request
     */
    private function _setRequestData(Request $request)
    {
        if (in_array($request->getMethod(), ['PUT', 'PATCH'])) {
            $request->setBodies(Utils::parse_http_put());
        } else if ($request->getMethod() === 'POST') {
            $request->setBodies((object) $_POST);
        }

        $request->setParams((object) $this->_matches);

        if (!empty($_FILES)) {
            $request->setFiles((object) $_FILES);
        }



        return $request;
    }

    /**
     * @param $params
     * @return mixed|string
     */
    public function getUrl($params)
    {
        $endpoint = $this->_endpoint;
        foreach ($params as $k => $v) {
            $endpoint = str_replace(":$k", $v, $endpoint);
        }

        return $endpoint;
    }

    /**
     * Create an error if user does not has a valid token
     */
    public function needToken()
    {
        $errorToken = false;

        if ($this->_needToken === true) {
            // If token does not exist : generate error
            if (empty(Utils::getHeader('X-Auth-Token'))) {

                $errorToken = true;

            } else {

                if (explode('/', Utils::getHeader('X-Auth-Token'))[1] != sha1($_SERVER['REMOTE_ADDR'])) {
                    // If the second part of token (the IP) is not equal to the IP of user which has sent the request : generator error
                    $errorToken = true;
                }

                $token = Auth::findFirst(['token' => Utils::getHeader('X-Auth-Token')]);

                // If token is invalid : generator error
                // If token expire (set date of expiration in Config file) : generate error
                $expire = Config::get('token')['expire'];

                if (null == $token || (null != $expire && $token->date + $expire < time())) {
                    $errorToken = true;
                }
            }

            if ($errorToken) return ["error" => "E_A002"];
            elseif (!empty($this->_needRole)) return $this->needRole($token);
        }
    }

    /**
     * Create an error if user does not has valid role
     * Use if the token is valid
     * Example use :
     *  ['>' => 2] -> the user must to be superior to the role 2
     * @param $token
     */
    public function needRole($token)
    {
        $user = User::getById($token->user_id);
        $id = $user->role_id;
        $errorRole = false;

        foreach ($this->_needRole as $k => $v) {

            switch ($k) {

                case '>':
                    if ($id <= $v) $errorRole = true;
                    break;

                case '>=':
                    if ($id < $v) $errorRole = true;
                    break;

                case '<':
                    if ($id >= $v) $errorRole = true;
                    break;

                case '<=':
                    if ($id > $v) $errorRole = true;
                    break;

                case '=':
                    if ($id != $v) $errorRole = true;
                    break;

            }

        }

        if ($errorRole) return ["error" => "E_A003"];
    }

    /**
     * Check the require bodies
     * If there are a bodies without value
     */
    public function checkBodies()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if (in_array($method, ['PUT', 'PATCH'])) {
            $data = Utils::parse_http_put();
        } else if ($method === 'POST') {
            $data = (object) $_POST;
        }

        $requireKeys = [];
        $requireTypes = [];

        foreach ($this->_bodies as $key => $type) {
            $nameOfKey = ($key[0] === '*') ? substr($key, 1, strlen($key)) : $key;

            if ($key[0] === '*' || property_exists($data, $nameOfKey)) {
                $requireKeys[] = $nameOfKey;
                $requireTypes[$nameOfKey] = ucfirst($type);
            }
        }
        if (isset($data)) {
            return Utils::checkPropsInObject($data, $requireKeys, $requireTypes);
        }
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->_callable;
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->_matches;
    }

    /**
     * @return array
     */
    public function getTypeParams()
    {
        return $this->_typeParams;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @return array
     */
    public function getBodies()
    {
        return $this->_bodies;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    public function getNeedToken()
    {
        return $this->_needToken;
    }

    /**
     * @return array
     */
    public function getNeedRole()
    {
        return $this->_needRole;
    }
}
