<?php
namespace Kernel\Orm;

use Kernel\Tools\Utils;

class Connection
{
    private static $pdo;

    /**
     * @throws OrmException
     */
    private static function init()
    {
        try {
            $db = Utils::getConfigElement('database');
            $pdo = new \PDO('mysql:dbname='. $db['dbname'] .';host='. $db['host'], $db['user'], $db['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $pdo->exec('SET NAMES \'utf8\'');
            $pdo->query('SET NAMES \'utf8\'');
            $pdo->prepare('SET NAMES \'utf8\'');

            self::$pdo = $pdo;

        } catch(\Exception $e) {
            // Register log
            $logger = Logger::getInstance(Logger::LOG_ERROR);
            $logger->write($e->getMessage() . ". Code : " . OrmException::CONNECTION);

            die($e->getMessage());
        }
    }

    /**
     * @return \PDO
     */
    public static function getPdo()
    {
        if (null == self::$pdo) { self::init(); };

        return self::$pdo;
    }

}
