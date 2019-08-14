<?php
namespace Controllers;

use function Couchbase\basicEncoderV1;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Kernel\Config;
use Kernel\Http\ApiCode;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Utils;

class Docs extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @param array $routes
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function homePage(Request $request, Response $response, $routes)
    {
        $response->getBody()
            ->add(self::getDescription(), "description")
            ->add(self::getHttpRequests(), "httpRequests")
            ->add(self::getApiCodes(), "responseCodes")
            ->add(Utils::getConfigElement('regex'), "regex")
            ->add(self::getRefs($routes), "refs");

        return $response->toView('home');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @param array $routes
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function routesPage(Request $request, Response $response, $routes)
    {
        $response->getBody()
            ->add(self::getDescription(), "description")
            ->add(self::getHttpRequests(), "httpRequests")
            ->add(self::getApiCodes(), "responseCodes")
            ->add(Utils::getConfigElement('regex'), "regex")
            ->add(self::getRefs($routes), "refs");

        return $response->toView('routes');
    }

    /**
     * Create the docs with
     * @param $routes
     * @return array
     */
    private static function getRefs($routes)
    {
        $refs = [];

        foreach ($routes as $route) {
            if (!in_array($route->getPath(), ['docs', 'docs/routes'])) {

                $ref = explode('/', $route->getPath())[0];

                $route = self::getProperties($route);

                $refs[$ref][] = $route;
            }
        }

        return $refs;
    }

    /**
     * Get the php doc properties of the route
     * @param $route
     */
    private static function getProperties($initialRoute)
    {
        $params = explode('#', $initialRoute->getCallable());
        $controller = "\Controllers\\$params[0]";

        $annotations = self::getPhpDoc($controller, $params[1]);

        // Create route with empty codes
        $route = [
            "codes" => []
        ];

        foreach ($annotations as $k => $v) { $route[$k] = $v; }

        foreach ($initialRoute->getProperties() as $k => $v) {
            $propRenammed = implode(array_map('ucfirst', explode('_', $k)));
            $getter = "get$propRenammed";

            $route[$k] = $initialRoute->$getter();
        }

        if ($initialRoute->getToken() && !array_key_exists("E_A002", $route["codes"])) {
            $route["codes"]["E_A002"] = ApiCode::E_A002;
        }

        if (!empty($initialRoute->getBodies())) {
            foreach ($initialRoute->getBodies() as $body) {
                if ($body["required"] && !array_key_exists("E_A004", $route["codes"])) {
                    $route["codes"]["E_A004"] = ApiCode::E_A004;
                    break;
                }
            }
        }

        return $route;
    }

    /**
     * Add a new error in the doc of the route
     * @param $code
     * @param null $key
     * @return array
     */
    private static function addErrorToRoute($code, $key = null)
    {
        $responseCodes = self::getApiCodes();

        foreach ($responseCodes[1] as $resCode => $status) {
            if ($resCode == $code) {
                return [
                    'code' => $code,
                    'status' => $status['status'],
                    'message' => (null != $key) ? preg_replace('/:key/', $key, $status['message']) : $status['message']
                ];
            }
        }
    }

    /**
     * List of http requests methods allowed
     * @return array
     */
    private static function getHttpRequests()
    {
        return [
            [
                'title' => 'GET',
                'content' => 'Retrieve a resource and list of resources'
            ],
            [
                'title' => 'POST',
                'content' => 'Add a new resource'
            ],
            [
                'title' => 'PUT',
                'content' => 'Update a resource with an identifier'
            ],
            [
                'title' => 'DELETE',
                'content' => 'Delete a resource with an identifier'
            ]
        ];
    }

    /**
     * List of response codes (success and error)
     * @return array
     */
    private static function getApiCodes()
    {
        $class = new \ReflectionClass(ApiCode::class);
        $apiCodes = $class->getConstants();

        $successCodes = [];
        $errorCodes = [];
        foreach ($apiCodes as $code => $content) {

            if (preg_match('#_G#', $code)) {
                $content['method'] = 'get';
            } elseif (preg_match('#_PO#', $code)) {
                $content['method'] = 'post';
            } elseif (preg_match('#_PU#', $code)) {
                $content['method'] = 'put';
            } elseif (preg_match('#_D#', $code)) {
                $content['method'] = 'delete';
            } elseif (preg_match('#_A#', $code)) {
                $content['method'] = 'all';
            }

            if ($code[0] == 'S') {
                $successCodes[$code] = $content;
            } else {
                $errorCodes[$code] = $content;
            }
        }

        return [$successCodes, $errorCodes];
    }

    /**
     * Set a description of the API
     * @return string
     */
    private static function getDescription()
    {
        $project = Utils::getConfigElement('project') . ' v' . Utils::getConfigElement('version');
        return "Welcome to the documentation of the <b>$project</b>.<br>
        This documentation allow you to understand this RESTful API. To navigate in the documentation, 
        you can use the menu by clicking on the menu icon <i class=\"material-icons tiny\">menu</i> at top left.<br>
        Also, in this home page, you can see all methods of HTTPs requests allowed in the API at right and at bottom the success or error codes to which refer to.";
    }

    /**
     * @param $class
     * @param $method
     * @return array
     * @throws \ReflectionException
     */
    public static function getPhpDoc($class, $method = null)
    {
        if (null != $method) {
            $doc = (new \ReflectionMethod($class, $method))->getDocComment();
        } else {
            $doc = (new \ReflectionClass($class))->getDocComment();
        }

        $additionalTags = [];

        foreach (Utils::getConfigElement("docTags") as $k => $v) {
            $value = "\Jasny\PhpdocParser\Tag\\$v";
            $additionalTags[] = new $value($k);
        }

        $tags = PhpDocumentor::tags()->with($additionalTags);

        $parser = new PhpdocParser($tags);
        $annotations = $parser->parse($doc);

        if (isset($annotations["codes"])) {
            $codes = [];

            foreach ($annotations["codes"] as $code) {
                $codes[$code] = constant("\Kernel\Http\ApiCode::$code");
            }

            $annotations["codes"] = $codes;
        }

        return $annotations;
    }
}
