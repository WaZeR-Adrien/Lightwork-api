<?php
namespace Controllers;

use Kernel\Config;
use Kernel\Http\Request;
use Kernel\Http\Response;

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
        $response->setData([
            'description' => self::getDescription(),
            'httpRequests' => self::getHttpRequests(),
            'responseCodes' => self::getResponseCodes(),
            'regex' => Config::get('regex'),
            'refs' => self::getRefs($routes)
        ]);

        return $response->view('home');
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
    public static function routesPage(Request $request, Response $response, $params, $routes)
    {
        $response->setData([
            'description' => self::getDescription(),
            'httpRequests' => self::getHttpRequests(),
            'responseCodes' => self::getResponseCodes(),
            'regex' => Config::get('regex'),
            'refs' => self::getRefs($routes)
        ]);

        return $response->view('routes');
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
                $ref = explode('/', $route->getEndpoint())[0];

                $route->errors = [];
                if ($route->getNeedToken()) $route->errors[] = self::addErrorToRoute('E_A002');
                if (!empty($route->getNeedRole())) $route->errors[] = self::addErrorToRoute('E_A003');

                foreach ($route->getBodies() as $key => $type) {
                    if ($key[0] === "*") $route->errors[] = self::addErrorToRoute('E_A005', substr($key, 1, strlen($key)));
                }

                $refs[$ref][] = $route;
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
        $responseCodes = self::getResponseCodes();

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
    private static function getResponseCodes()
    {
        $responseCodes = Config::get('responseCode');

        $successCodes = [];
        $errorCodes = [];
        foreach ($responseCodes as $code => $status) {
            if (preg_match('#_G#', $code)) {
                $status['method'] = 'get';
            } elseif (preg_match('#_PO#', $code)) {
                $status['method'] = 'post';
            } elseif (preg_match('#_PU#', $code)) {
                $status['method'] = 'put';
            } elseif (preg_match('#_D#', $code)) {
                $status['method'] = 'delete';
            } elseif (preg_match('#_A#', $code)) {
                $status['method'] = 'all';
            }

            if ($code[0] == 'S') {
                $successCodes[$code] = $status;
            } else {
                $errorCodes[$code] = $status;
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

}
