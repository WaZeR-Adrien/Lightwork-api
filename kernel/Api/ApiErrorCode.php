<?php
namespace Kernel\Api;

use Kernel\Http\StatusCode;

class ApiErrorCode
{
    public const NF404 = [
        "code" => "NF404",
        "status" => StatusCode::HTTP_NOT_FOUND,
        "message" => "Content not found."
    ];

    public const A001 = [
        "code" => "A001",
        "status" => StatusCode::HTTP_UNAUTHORIZED,
        "message" => "Incorrect information of connection. The email or the password does not valid."
    ];
}
