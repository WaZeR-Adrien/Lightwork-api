<?php
namespace Kernel\Router;

use Controllers\Docs;
use Kernel\Config;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Collection\Collection;
use Kernel\Tools\Utils;
use Models\Auth;
use Models\User;
use function PHPSTORM_META\type;

class Route
{
    /**
     * Method
     * @var string
     */
    private $method;

    /**
     * Endpoint
     * @var string
     */
    private $path;

    /**
     * Method / function to call
     * Ex: MyController#myMethod
     * @var string
     */
    private $callable;

    /**
     * If need token
     * @var boolean
     */
    private $token;

    /**
     * Matches params
     * @var array
     */
    private $matches = [];

    /**
     * Arguments
     * @var array
     */
    private $args = [];

    /**
     * Type of arguments
     * @var array
     */
    private $typeArgs = [];

    /**
     * Bodies
     * @var array
     */
    private $bodies = [];

    /**
     * Name / Description for the documentation
     * @var string
     */
    private $name;

    /**
     * Codes for the response
     * @var array
     */
    private $codes = [];

    /**
     * Type of the render (json | xml...)
     * @var string
     */
    private $render;


    /**
     * Route constructor
     * @param $method
     * @param $path
     * @param $callable
     * @param $token
     * @param $args
     * @param $bodies
     */
    public function __construct($method, $path, $callable, $token, $args, $bodies)
    {
        $this->method = $method;
        $this->path = trim($path, '/');
        $this->callable = $callable;
        $this->token = $token;
        $this->typeArgs = $args;
        $this->bodies = $bodies;

        $this->editArgs($args);

        $this->setProperties();
    }

    private function setProperties()
    {
        $params = explode('#', $this->callable);
        $controller = "\Controllers\\$params[0]";

        $annotations = Docs::getPhpDoc($controller, $params[1]);

        foreach ($annotations as $k => $v) {
            if (property_exists($this, $k) && empty($this->$k)) {

                $this->$k = $v;
            }
        }

        if ($this->token && !array_key_exists("E_A002", $this->codes)) {
            $this->codes["E_A002"] = Utils::getConfigElement("apiCode")["E_A002"];
        }

        if (!empty($this->bodies)) {
            foreach ($this->bodies as $key => $type) {
                if ($key[0] === '*' && !array_key_exists("E_A004", $this->codes)) {
                    $this->codes["E_A004"] = Utils::getConfigElement("apiCode")["E_A004"];
                }
            }
        }
    }

    /**
     * Edit args with regex
     * @param $args
     */
    private function editArgs($args)
    {
        if (!empty($args)) {
            foreach ($args as $key => $value) {

                $value = Config::setRegex(ucfirst($value));

                $this->args[$key] = str_replace('(', '(?:', $value);
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
        $endpoint = preg_replace_callback('#:([\w]+)#', [$this, 'argMatch'], $this->path);
        $reg = "#^$endpoint$#i";

        // Get values
        if (!preg_match($reg, $currentUrl, $matchesValues)) {
            return false;
        }
        array_shift($matchesValues);

        // Get keys
        preg_match_all('#:([\w]+)#', $this->path, $matchesKeys);

        $matchesKeys = $matchesKeys[1];
        $matches = [];

        // Match key with value
        for ($i = 0; $i < count($matchesKeys); $i++) {
            $matches[$matchesKeys[$i]] = $matchesValues[$i];
        }
        $this->matches = $matches;
        return true;
    }

    /**
     * @param $match
     * @return string
     */
    private function argMatch($match)
    {
        if (isset($this->args[$match[1]])) {
            $reg = '(' . $this->args[$match[1]] . ')';
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
        // Create request
        $request = new Request($_SERVER['REQUEST_METHOD']);
        $this->setRequestData($request);

        // Create response
        $response = new Response($this);

        // If there are errors with token : generate error
        $needToken = $this->needToken();
        if (!empty($needToken["error"])) {
            return $response->fromApi($needToken["error"])->toJson();
        }

        // If there are errors with data bodies : generate error
        $bodies = $this->checkBodies();
        if (!empty($bodies["error"])) {
            return $response->fromApi($bodies["error"], $bodies["key"])->toJson();
        }

        $token = Utils::getHeader("X-Auth-Token");
        if (!empty($token)) {
            $request->getHeaders()->add($token, "X-Auth-Token");
        }

        // Request - Response - Routes
        $data = [$request, $response, $router->getAllRoutes()];

        if (is_string($this->callable)) {
            $params = explode('#', $this->callable);
            $controller = "Controllers\\$params[0]";

            return call_user_func_array([new $controller(), $params[1]], $data);
        }
        else {
            return call_user_func_array($this->callable, $data);
        }
    }

    /**
     * Set data
     * @param Request $request
     * @return Request
     */
    private function setRequestData(Request $request)
    {
        if (in_array($request->getMethod(), ['PUT', 'PATCH'])) {
            $request->setBody( new Collection((array) Utils::parse_http_put()) );
        } else if ($request->getMethod() === 'POST') {
            $request->setBody( new Collection($_POST) );
        }

        $request->setArgs( new Collection($this->matches) );

        if (!empty($_FILES)) {
            $request->setFiles( new Collection($_FILES) );
        }



        return $request;
    }

    /**
     * @param $params
     * @return mixed|string
     */
    public function getUrl($params)
    {
        $endpoint = $this->path;
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

        if ($this->token === true) {
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
        }
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

        foreach ($this->bodies as $key => $type) {
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
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @return array
     */
    public function getBodies()
    {
        return $this->bodies;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array
     */
    public function getNeedRole()
    {
        return $this->_needRole;
    }

    /**
     * @return array
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * @param array $codes
     */
    public function setCodes($codes)
    {
        $this->codes = $codes;
    }

    /**
     * @return string
     */
    public function getRender()
    {
        return $this->render;
    }

    /**
     * @param string $render
     */
    public function setRender($render)
    {
        $this->render = $render;
    }

    /**
     * @return array
     */
    public function getTypeArgs()
    {
        return $this->typeArgs;
    }

    /**
     * @param array $typeArgs
     */
    public function setTypeArgs($typeArgs)
    {
        $this->typeArgs = $typeArgs;
    }
}
