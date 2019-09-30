<?php
namespace Kernel\Api;

class ApiException extends \RuntimeException
{
    /**
     * Http status
     * @var string
     */
    private $status;

    public function __construct(array $apiCode, string $key = null)
    {
        $this->status = $apiCode["status"];
        $this->code = $apiCode["code"];
        $this->message = preg_replace('/:key/', $key, $apiCode["message"]);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }


}
