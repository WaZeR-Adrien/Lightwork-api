<?php
namespace Kernel;

class Config
{
    private static $_config = [
        'database' => [
            'host' => 'YOUR_HOST',
            'db'   => 'YOUR_DB',
            'user' => 'YOUR_USER',
            'pw'   => 'YOUR_PASSWORD'
        ],
        'mail' => [
            'host'     => 'YOUR_SMTP_HOST',
            'username' => 'YOUR_SMTP_USER/EMAIL',
            'pw' => 'YOUR_SMTP_PASSWORD',
        ],
        'reg' => [
            'String'     => '\w+',
            'StringAcc'  => '[\p{L}\p{Nd}\s-_]+',
            'Int'        => '\d+',
            'Boolean'    => '(true|false)',
            'BooleanInt' => '(1|0)',
            'FrenchDate' => '(0?[1-9]|[12][0-9]|3[01])\/(0?[1-9]|1[0-2])\/\d{4}',
            'UsDate'     => '\d{4}\-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01])',
            'Datetime'   => '\d{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9])(:([0-9]|[0-5][0-9]))?'
        ],
        'token' => [
            'expire' => 604800 // 7 days in seconds (set NULL if you want no expiration token)1
        ],
        'path' => []
    ];

    public static function getDatabase()
    {
        return self::$_config['database'];
    }

    public static function getMail()
    {
        return self::$_config['mail'];
    }

    public static function getRegex()
    {
        return self::$_config['reg'];
    }

    public static function setRegex($type)
    {
        if ($type != '') {
            if (in_array($type, self::getRegex())) {
                // Return the regex with the type (like String => \w+)
                return self::getRegex()[$type];
            }

            // Return directly the regex
            return $type;
        }

        // Return all regex
        return '.';
    }

    public static function getToken()
    {
        return self::$_config['token'];
    }

    public static function getPath()
    {
        return self::$_config['path'];
    }

    public static function getResponse($code)
    {
        $json = file_get_contents("../kernel/status.json");
        $res = json_decode($json);
        $key = array_search($code, array_column($res, 'code'));
        return $res[$key];
    }
}
