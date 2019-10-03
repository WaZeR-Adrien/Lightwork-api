<?php
namespace Kernel\Api;

use Kernel\Http\StatusCode;

class ApiErrorCode
{
    public const PAGE001 = [
        "code" => "PAGE001",
        "status" => StatusCode::HTTP_BAD_REQUEST,
        "message" => "Incorrect information of pageable. The length must be int superior to 0, :key given."
    ];

    public const PAGE002 = [
        "code" => "PAGE002",
        "status" => StatusCode::HTTP_BAD_REQUEST,
        "message" => "Incorrect information of pageable. The current page must be int superior to 0, :key given."
    ];

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
