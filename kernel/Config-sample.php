<?php
namespace Kernel;

use Kernel\Http\StatusCode;

class Config
{
    private static $_config = [
        'project' => 'Lightwork API',
        'version' => '4.0.0',
        'database' => [
            'host' => 'YOUR_HOST',
            'db'   => 'YOUR_DB',
            'user' => 'YOUR_USER',
            'pw'   => 'YOUR_PASSWORD'
        ],
        'mail' => [
            'host'     => 'YOUR_SMTP_HOST',
            'username' => 'YOUR_SMTP_USER/EMAIL',
            'pw'       => 'YOUR_SMTP_PASSWORD',
        ],
        'captcha' => [
            'apiSite'   => 'YOUR_API_PUBLIC_KEY',
            'apiSecret' => 'YOUR_API_PRIVATE_KEY'
        ],
        'regex' => [
            'String'     => '\w+',
            'StringAcc'  => '[\p{L}\p{Nd}\s-_]+',
            'Int'        => '\d+',
            'Boolean'    => '(true|false)',
            'BooleanInt' => '(1|0)',
            'FrenchDate' => '(0?[1-9]|[12][0-9]|3[01])\/(0?[1-9]|1[0-2])\/\d{4}',
            'UsDate'     => '\d{4}\-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01])',
            'Datetime'   => '\d{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9])(:([0-9]|[0-5][0-9]))?'
        ],
        'responseCode' => [
            // Errors
            'E_A001' => [
                "status" => StatusCode::HTTP_NOT_FOUND,
                "message" => "This page does not exist."
            ],
            'E_A002' => [
                "status" => StatusCode::HTTP_UNAUTHORIZED,
                "message" => "You need a valid authentication to the API. You need to send a valid token (which has not expired and whose the IP address correspond to yours)."
            ],
            'E_A003' => [
                "status" => StatusCode::HTTP_UNAUTHORIZED,
                "message" => "You need a valid role to access to the API."
            ],
            'E_A004' => [
                "status" => StatusCode::HTTP_UNAUTHORIZED,
                "message" => "Incorrect information of connection. The email or the password does not valid."
            ],
            'E_A005' => [
                "status" => StatusCode::HTTP_UNPROCESSABLE_ENTITY,
                "message" => "The :key may not exist or can contain an error in the name or in the format."
            ],
            'E_A006' => [
                "status" => StatusCode::HTTP_UNPROCESSABLE_ENTITY,
                "message" => "No content found with this id in the table :key."
            ],

            // Success
            'S_G001' => [
                "status" => StatusCode::HTTP_OK,
                "message" => "The content is retrieved with success."
            ],
            'S_PO001' => [
                "status" => StatusCode::HTTP_CREATED,
                "message" => "The content is added with success."
            ],
            'S_PU001' => [
                "status" => StatusCode::HTTP_OK,
                "message" => "The content is updated with success."
            ],
            'S_D001' => [
                "status" => StatusCode::HTTP_OK,
                "message" => "The content is deleted with success."
            ]
        ],
        'token' => [
            'expire' => 604800 // 7 days in seconds (set NULL if you want no expiration token)1
        ],
        'path' => []
    ];

    /**
     * @param string $config
     * @return mixed
     */
    public static function get($config)
    {
        return self::$_config[$config];
    }

    /**
     * @param $type
     * @return string
     */
    public static function setRegex($type)
    {
        if ($type != '') {
            if (key_exists($type, self::get('regex'))) {
                // Return the regex with the type (like String => \w+)
                return self::get('regex')[$type];
            }

            // Return directly the regex
            return $type;
        }

        // Return all regex
        return '.';
    }
}
