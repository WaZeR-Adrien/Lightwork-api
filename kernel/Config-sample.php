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
        'reg' => [
            'int'       => '/^[0-9]+$/i',
            'string'    => '/^[a-zA-Z0-9 ]+$/i',
            'stringacc' => '/^[\p{L}\p{Nd}\s-_]+$/i',
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

    public static function getReg()
    {
        return self::$_config['reg'];
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
