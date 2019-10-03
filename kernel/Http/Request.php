<?php
namespace Kernel\Http;

use AdrienM\Collection\Collection;
use Kernel\Orm\Pageable;

class Request
{
    /**
     * Request method
     * @var string
     */
    private $method;

    /**
     * Query params Collection
     * @var Collection
     */
    private $queryParams;

    /**
     * Arguments of query Collection
     * @var Collection
     */
    private $args;

    /**
     * Page for the ORM
     * @var Pageable
     */
    private $page;
    
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
        $this->queryParams = $this->args = $this->headers = $this->body = $this->files = new Collection();
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
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param Collection $queryParams
     */
    public function setQueryParams(Collection $queryParams)
    {
        $this->queryParams = $queryParams;
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
     * @return Pageable
     */
    public function getPage(): Pageable
    {
        return $this->page;
    }

    /**
     * @param Pageable $page
     */
    public function setPage(Pageable $page): void
    {
        $this->page = $page;
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
