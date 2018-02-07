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
            'strint'    => '/^[a-zA-Z0-9 ]+$/i',
            'strintacc' => '/^[\p{L}\p{Nd}\s-_]+$/i',
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

    public static function getPath()
    {
        return self::$_config['path'];
    }
}
