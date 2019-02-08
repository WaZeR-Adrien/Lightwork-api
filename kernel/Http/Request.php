<?php
namespace Kernel\Http;

class Request
{
    /**
     * Request method
     * @var string
     */
    private $method;

    /**
     * Params of request
     * @var object
     */
    private $params;

    /**
     * Bodies data of request
     * @var object
     */
    private $bodies;

    /**
     * Files of request
     * @var object
     */
    private $files;

    /**
     * Endpoint
     * @var string
     */
    private $endpoint;

    /**
     * Request constructor.
     * @param string $method
     */
    public function __construct($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return object
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param object $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return object
     */
    public function getBodies()
    {
        return $this->bodies;
    }

    /**
     * @param object $bodies
     */
    public function setBodies($bodies)
    {
        $this->bodies = $bodies;
    }

    /**
     * @return object
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param object $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }
}