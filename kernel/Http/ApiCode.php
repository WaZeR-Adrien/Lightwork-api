<?php
namespace Kernel\Http;

use Kernel\Config;

class ApiCode implements \JsonSerializable
{
    /**
     * Code
     * @var string
     */
    private $code;

    /**
     * Http status
     * @var int
     */
    private $status;

    /**
     * Message / Content
     * @var string
     */
    private $message;

    /**
     * RenderCode constructor.
     * @param string $code
     */
    public function __construct($code)
    {
        try {
            if (isset(Config::get('apiCode')[$code])) {
                $resCode = Config::get('apiCode')[$code];

                $this->code = $code;
                $this->status = $resCode['status'];
                $this->message = $resCode['message'];
            } else {
                throw new \Exception("Api code does not exist");
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $props = get_object_vars($this);

        return $props;
    }
}