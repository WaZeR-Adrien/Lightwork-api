<?php
namespace Kernel\Http;

use AdrienM\Collection\Collection;

class Request
{
    /**
     * Request method
     * @var string
     */
    private $method;

    /**
     * Params Collection
     * @var Collection
     */
    private $args;
    
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
     * Files Collection
     * @var Collection
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
        $this->args = $this->headers = $this->body = $this->files = new Collection();
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
     * @return Collection
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param Collection $args
     */
    public function setArgs(Collection $args)
    {
        $this->args = $args;
    }

    /**
     * @return Collection
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param Collection $headers
     */
    public function setHeaders(Collection $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return Collection
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param Collection $body
     */
    public function setBody(Collection $body)
    {
        $this->body = $body;
    }

    /**
     * @return Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param Collection $files
     */
    public function setFiles(Collection $files)
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
