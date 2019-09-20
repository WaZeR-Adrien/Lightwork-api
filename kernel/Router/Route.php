<?php
namespace Kernel\Router;

use Controllers\Docs;
use Kernel\Config;
use Kernel\Http\Request;
use Kernel\Http\Response;
use AdrienM\Collection\Collection;
use Kernel\Tools\Utils;
use Models\Auth;
use Models\User;

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
    private $token = null;

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
     * Bodies
     * @var array
     */
    private $bodies = [];

    /**
     * Route constructor
     * @param $method
     * @param $path
     * @param $callable
     */
    public function __construct($method, $path, $callable)
    {
        $this->method = $method;
        $this->path = trim($path, '/');
        $this->callable = $callable;
    }

    /**
     * Check if currentUrl match with the url of this route
     * @param $currentUrl
     * @return bool
     */
    public function match($currentUrl)
    {
        $currentUrl = trim($currentUrl, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'argMatch'], $this->path);
        $reg = "#^$path$#i";

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
        $reg = '([^/]+)';

        if (isset($this->args[$match[1]])) {
            $reg = '(' . $this->args[$match[1]]["regex"] . ')';
        }

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
        $bodies = $this->checkBodies( (object) $request->getBody()->getAll() );
        if (!empty($bodies["error"])) {
            return $response->fromApi($bodies["error"], $bodies["key"])->toJson();
        }

        // Get the token
        $token = Utils::getHeader("X-Auth-Token");
        if (!empty($token)) {
            $request->getHeaders()->add($token, "X-Auth-Token");
        }

        // Request - Response - Routes
        $data = [$request, $response, $router->getAllRoutes()];

        if (is_string($this->callable)) {
            $callable = explode('#', $this->callable);
            $controller = "Controllers\\$callable[0]";

            return call_user_func_array([new $controller(), $callable[1]], $data);
        } else {
            return call_user_func_array($this->callable, $data);
        }
    }

    /**
     * Set data
     * @param Request $request
     * @return Request
     */
    private function setRequestData(Request &$request)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $request->setBody( Collection::from(null != $data ? $data : []) );

        $request->setArgs( Collection::from($this->matches) );

        if (!empty($_FILES)) {
            $request->setFiles( Collection::from($_FILES) );
        }
    }

    /**
     * @param array $args
     * @return mixed|string
     */
    public function getUrl($args)
    {
        $path = $this->path;
        foreach ($args as $k => $v) {
            $path = str_replace(":$k", $v, $path);
        }

        return $path;
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
                $expire = Utils::getConfigElement('token')->expire;

                if (null == $token || (null != $expire && $token->getDate() + $expire < time())) {
                    $errorToken = true;
                }
            }

            if ($errorToken) return ["error" => "E_A002"];
        }

        return true;
    }

    /**
     * Check the require bodies
     * If there are a bodies without value
     */
    public function checkBodies($data)
    {
        $require = [
            "key" => [],
            "type" => []
        ];

        foreach ($this->bodies as $k => $body) {
            if ($body["required"] || property_exists($data, $k)) {
                $require["key"][] = $k;
                $require["type"][$k] = ucfirst($body["type"]);
            }
        }
        if (isset($data)) {
            return Utils::checkPropsInObject($data, $require["key"], $require["type"]);
        }
    }

    /**
     * @param string $name
     * @param string $type
     * @return Route
     */
    public function arg($name, $type)
    {
        $value = Utils::setRegex(ucfirst($type));

        $this->args[$name] = [
            "type" => $type,
            "regex" => str_replace('(', '(?:', $value)
        ];

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param bool $required
     * @return Route
     */
    public function body($name, $type, $required = false)
    {
        $this->bodies[$name] = [
            "type" => $type,
            "required" => $required
        ];

        return $this;
    }

    /**
     * @param bool $required
     * @return Route
     */
    public function token($required = true)
    {
        $this->token = $required;

        return $this;
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
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @return array
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }
}
