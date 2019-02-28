<?php
namespace Controllers;

use function Couchbase\basicEncoderV1;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Kernel\Config;
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
            ->add(Config::get('regex'), "regex")
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
            ->add(Config::get('regex'), "regex")
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
            if (!in_array($route->getEndpoint(), ['docs', 'docs/routes'])) {

                $params = explode('#', $route->getCallable());
                $controller = "\Controllers\\$params[0]";

                $annotations = self::getPhpDoc($controller, $params[1]);

                $ref = explode('/', $route->getEndpoint())[0];

                $annotations["name"] = $route->getName();
                $annotations["endpoint"] = $route->getEndpoint();
                $annotations["method"] = $route->getMethod();

                switch ($annotations["render"]) {
                    case "json":
                        $annotations["contentType"] = "application/json";
                        break;

                    default:
                        $annotations["contentType"] = "text/" . $annotations["render"];
                }

                $refs[$ref][] = $annotations;
            }
        }

        return $refs;
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
        $apiCodes = Config::get('apiCode');

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
        $project = Config::get('project') . ' v' . Config::get('version');
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
    public static function getPhpDoc($class, $method)
    {
        $doc = (new \ReflectionMethod($class, $method))->getDocComment();

        $additionalTags = [];

        foreach (Utils::getConfigElement("docTags") as $key => $value) {
            $value = "\Jasny\PhpdocParser\Tag\\$value";
            $additionalTags[] = new $value($key);
        }

        $tags = PhpDocumentor::tags()->with($additionalTags);

        $parser = new PhpdocParser($tags);
        $annotations = $parser->parse($doc);

        if (isset($annotations["codes"])) {
            $codes = [];

            foreach ($annotations["codes"] as $code) {
                $codes[$code] = Utils::getConfigElement("apiCode")[$code];
            }

            $annotations["codes"] = $codes;
        }

        return $annotations;
    }
}
