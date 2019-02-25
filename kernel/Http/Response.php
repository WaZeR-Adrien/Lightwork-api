<?php
namespace Kernel\Http;

use GreenCape\Xml\Converter;
use Kernel\Logs\Log;
use Kernel\Router\Route;
use Kernel\Tools\Collection\Collection;
use Kernel\Twig;
use Symfony\Component\Yaml\Yaml;

class Response
{
    /**
     * Http API code
     * @var ApiCode
     */
    private $apiCode;

    /**
     * Headers Collection
     * @var Collection
     */
    private $headers;

    /**
     * Body Collection
     * @var Collection
     */
    private $body;

    /**
     * Route
     * @var Route
     */
    private $route;

    /**
     * Content to show (View / JSON / XML / YAML...)
     * @var mixed
     */
    private $content = "";

    /**
     * Response constructor.
     * @param Route $route
     * @param string $contentType
     */
    public function __construct(Route $route = null, $contentType = "text/html; charset=UTF-8")
    {
        $this->route = $route;
        $this->headers = new Collection();
        $this->headers->add($contentType, "Content-Type");
        $this->body = new Collection();
    }

    /**
     * @return ApiCode
     */
    public function getApiCode()
    {
        return $this->apiCode;
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
     * @return Collection
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return Collection
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Add the event in the logs
     * @param string $key
     */
    private function addEventLog($key)
    {
        $date = date('d/m/Y H:i:s');

        $ip = $_SERVER['REMOTE_ADDR'];

        // Create new log
        $log = new Log(
            $this->apiCode->getCode(),
            $key,
            $date,
            $this->apiCode->getStatus(),
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
    public function fromApi($code, $key = null)
    {
        // Get type of response by first letter of the code
        $type = $code[0] == "S" ? "success" : "error";

        // Create new response code
        $this->apiCode = new ApiCode($code);

        if (null != $key) {
            // It's the target key (when there are a problem for example)
            $this->apiCode->setMessage(
                preg_replace('/:key/', $key, $this->apiCode->getMessage())
            );
        }

        // Store error in logs
        if ($type == "error") {
            self::addEventLog($key);
        }

        // Init the content by concatenating of success/error with responseCode and of data
        $body = new Collection();

        $body->add($this->apiCode->jsonSerialize(), $type);

        if (!$this->body->isEmpty()) {
            $body->add($this->body->getAll(), "data");
        }

        // Replace body
        $this->body->purge()->push($body);

        return $this;
    }

    /**
     * Convert the body to JSON format
     */
    public function toJson()
    {
        // Set Content Type to JSON
        $this->headers->add("application/json", "Content-Type");

        // Convert the content to JSON
        $this->content =
            json_encode($this->body->getAll());

        return $this;
    }

    /**
     * Convert the body to XML format
     */
    public function toXml()
    {
        // Set Content Type to XML
        $this->headers->add("text/xml; charset=UTF-8", "Content-Type");

        // Recursive cast
        $body = json_decode(json_encode($this->body->getAll()), true);

        // Convert the content to XML
        $this->content =
            new Converter($body);

        return $this;
    }

    /**
     * Convert the body to YAML format
     */
    public function toYaml()
    {
        // Set Content Type to YAML
        $this->headers->add("text/yaml", "Content-Type");

        // Convert the content to YAML
        $this->content =
            Yaml::dump($this->body->getAll());

        return $this;
    }

    /**
     * Generate view with data
     * @param string $view
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function toView($view)
    {
        // Set Content Type to HTML
        $this->headers->add("text/html; charset=UTF-8", "Content-Type");

        $twig = Twig::getInstance();

        $this->content = $twig->render($view . '.html.twig', (array) $this->body->getAll());

        return $this;
    }
}
