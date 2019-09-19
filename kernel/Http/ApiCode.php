<?php
namespace Kernel\Http;

use AdrienM\Logger\Logger;

class ApiCode implements \JsonSerializable
{
    // Errors
    const E_A001 = [
        "status" => StatusCode::HTTP_NOT_FOUND,
        "message" => "This page does not exist."
    ];
    const E_A002 = [
        "status" => StatusCode::HTTP_UNAUTHORIZED,
        "message" => "You need a valid authentication to the API. You need to send a valid token (which has not expired and whose the IP address correspond to yours)."
    ];
    const E_A003 = [
        "status" => StatusCode::HTTP_UNAUTHORIZED,
        "message" => "Incorrect information of connection. The email or the password does not valid."
    ];
    const E_A004 = [
        "status" => StatusCode::HTTP_UNPROCESSABLE_ENTITY,
        "message" => "The :key may not exist or can contain an error in the name or in the format."
    ];
    const E_A005 = [
        "status" => StatusCode::HTTP_UNPROCESSABLE_ENTITY,
        "message" => "No content found with this id in the table :key."
    ];

    // Success
    const S_G001 = [
        "status" => StatusCode::HTTP_OK,
        "message" => "The content is retrieved with success."
    ];
    const S_PO001 = [
        "status" => StatusCode::HTTP_CREATED,
        "message" => "The content is added with success."
    ];
    const S_PU001 = [
        "status" => StatusCode::HTTP_OK,
        "message" => "The content is updated with success."
    ];
    const S_D001 = [
        "status" => StatusCode::HTTP_OK,
        "message" => "The content is deleted with success."
    ];

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
            if (defined("self::$code")) {
                $resCode = constant("self::$code");

                $this->code = $code;
                $this->status = $resCode['status'];
                $this->message = $resCode['message'];
            } else {
                // Register log
                $logger = Logger::getInstance(null, Logger::LOG_ERROR);
                $logger->write("Api code $code does not exist. Code : " . HttpException::API_CODE_DOES_NOT_EXIST);

                throw new HttpException("Api code $code does not exist", HttpException::API_CODE_DOES_NOT_EXIST);
            }
        } catch (HttpException $e) {
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
