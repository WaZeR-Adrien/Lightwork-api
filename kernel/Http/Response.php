<?php
namespace Kernel\Http;

use GreenCape\Xml\Converter;
use Kernel\Loggers\HttpLogger;
use Kernel\Orm\Pageable;
use Kernel\Router\Route;
use AdrienM\Collection\Collection;
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
     * Http status code
     * @var int
     */
    private $status;

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
    public function __construct(Route $route = null, $status = 200, $contentType = "text/html; charset=UTF-8")
    {
        $this->route = $route;
        $this->status = $status;
        $this->headers = ( new Collection() )
            ->add($contentType, "Content-Type");
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
     * @param Collection $body
     */
    public function setBody($body)
    {
        $this->body = $body;
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
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Add the event in the logs
     * @param string $key
     */
    private function addEventLog($key)
    {
        // TODO : supprimer cette méthode ? et mettre le typage dans la classe
        $ip = $_SERVER['REMOTE_ADDR'];

        // Create new log
        $logger = HttpLogger::getInstance();

        // Content
        $method = (null != $this->route) ? $this->route->getMethod() : $_SERVER['REQUEST_METHOD'];
        $path = (null != $this->route) ? $this->route->getPath() : $_GET['url'];

        // Save in logs
        $logger->write($this->apiCode->getCode() . ",$key," . $this->getStatus() . ",$method,$path,$ip");
    }

    /**
     * Initialize data for the content before converted
     * @param Pageable|null $page
     * @return array
     */
    private function initializeData(Pageable $page = null): array
    {
        if ($page) {
            $page->setData($this->body);

            return $page->toArray();
        } else {
            return $this->body->getAll();
        }
    }

    /**
     * Convert the body to JSON format
     * @param Pageable|null $page
     * @return Response
     * @throws \AdrienM\Collection\CollectionException
     */
    public function toJson(Pageable $page = null)
    {
        // Set Content Type to JSON
        $this->headers->replace("Content-Type", "application/json");

        // Convert the content to JSON
        $this->content = json_encode($this->initializeData($page));

        return $this;
    }

    /**
     * Convert the body to XML format
     * @param Pageable|null $page
     * @return Response
     * @throws \AdrienM\Collection\CollectionException
     */
    public function toXml(Pageable $page = null)
    {
        // Set Content Type to XML
        $this->headers->replace("Content-Type", "text/xml; charset=UTF-8");

        // Recursive cast
        $body = json_decode(json_encode( $this->initializeData($page) ), true);

        // Convert the content to XML
        $this->content = new Converter($body);

        return $this;
    }

    /**
     * Convert the body to YAML format
     * @param Pageable|null $page
     * @return Response
     * @throws \AdrienM\Collection\CollectionException
     */
    public function toYaml(Pageable $page = null)
    {
        // Set Content Type to YAML
        $this->headers->replace("Content-Type", "text/yaml");

        // Convert the content to YAML
        $this->content = Yaml::dump($this->initializeData($page));

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
        $this->headers->replace("Content-Type", "text/html; charset=UTF-8");

        $twig = Twig::getInstance();

        $this->content = $twig->render($view . '.html.twig', (array) $this->body->getAll());

        return $this;
    }
}
