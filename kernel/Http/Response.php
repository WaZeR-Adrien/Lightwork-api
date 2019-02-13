<?php
namespace Kernel\Http;

use GreenCape\Xml\Converter;
use Kernel\Logs\Log;
use Kernel\Router\Route;
use Kernel\Twig;

class Response
{
    /**
     * Data in response
     * @var mixed
     */
    private $data = false;

    /**
     * Success in the render
     * True if success / False if error
     * @var bool
     */
    private $success = true;

    /**
     * Code of the response
     * @var ResponseCode
     */
    private $responseCode;

    /**
     * Headers
     * @var Headers
     */
    private $headers;

    /**
     * Body
     * @var Body
     */
    private $body;

    /**
     * Route
     * @var Route
     */
    private $route;

    /**
     * Response constructor.
     * @param Route $route
     * @param string $contentType
     */
    public function __construct(Route $route = null, $contentType = "text/html; charset=UTF-8")
    {
        $this->route = $route;
        $this->headers = new Headers();
        $this->headers->set("Content-Type", $contentType);
        $this->body = new Body();
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return ResponseCode
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param ResponseCode $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param Route $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return object
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Add the event in the logs
     * @param $key
     */
    private function addEventLog($key)
    {
        $date = date('d/m/Y H:i:s');

        $ip = $_SERVER['REMOTE_ADDR'];

        // Create new log
        $log = new Log(
            $this->responseCode->getCode(),
            $key,
            $date,
            $this->responseCode->getStatus(),
            (null != $this->route) ? $this->route->getMethod() : "",
            (null != $this->route) ? $this->route->getEndpoint() : "",
            $ip
        );

        // Save in logs
        $log->save();
    }

    /**
     * Generate render data in JSON / XML...
     * @param string $code
     * @param string $key
     * @return Response
     */
    public function render($code, $key = null)
    {
        // Get type of response by first letter of the code
        $this->success = $code[0] == 'S';

        // Create new response code
        $this->responseCode = new ResponseCode($code);

        if (null != $key) {
            // It's the target key (when there are a problem for example)
            $this->responseCode->setMessage(
                preg_replace('/:key/', $key, $this->responseCode->getMessage())
            );
        }

        // Store error in logs
        if (!$this->isSuccess()) {
            self::addEventLog($key);
        }

        // Init the content by concatenating of success/error with responseCode and of data
        $content = new \stdClass();

        $success = $this->isSuccess() ? 'success' : 'error';

        $content->$success = $this->responseCode->jsonSerialize();

        if ($this->data !== false) {
            $content->data = $this->data;
        }

        // Set the content serialized in the body
        $this->body->setContent(serialize($content));

        return $this;
    }

    /**
     * Convert the body to JSON format
     */
    public function toJson()
    {
        // Set Content Type to JSON
        $this->headers->set("Content-Type", "application/json");

        // Convert the content to JSON
        $this->body->setContent(
            json_encode(unserialize($this->body->getContent()))
        );

        return $this;
    }

    /**
     * Convert the body to XML format
     */
    public function toXml()
    {
        // Set Content Type to XML
        $this->headers->set("Content-Type", "text/xml; charset=UTF-8");

        // Convert the content to XML
        $this->body->setContent(
            new Converter((array) unserialize($this->body->getContent()))
        );

        return $this;
    }

    /**
     * Generate view with data
     * @param string $view
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function view($view)
    {
        $twig = Twig::init();

        echo $twig->render($view . '.html.twig', (array) $this->data);
        die();
    }
}