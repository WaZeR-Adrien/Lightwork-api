<?php
namespace Models\Dao;

use Controllers\Docs;
use Kernel\Orm\Database;
use AdrienM\Collection\Collection;
use Kernel\Tools\Utils;
use Models\Entity;

/**
 * Class Dao
 * @package Models\dao
 */
abstract class Dao
{
    use Database;

    /**
     * Fetch data with id
     * Example :
     * $user = new User(10);
     * $user->fetch(); // Retrieve all data from the user
     * @param Entity $obj
     * @param bool $recursive
     */
    public static function fetch($obj, bool $recursive = false)
    {
        foreach ($obj->toArray() as $k => $v) {
            $upper = Utils::toPascalCase($k);
            $getter = "get$upper";
            $setter = "set$upper";

            if (is_object($obj->$getter()) && null != $obj->$getter()->getId()) {
                /**
                 * @var Collection $schemas
                 */
                $schemas = $obj::getSchemas();

                if ($schemas->keyExists($k)) {
                    $dao = $schemas->get($k)["dao"];
                    $res = $dao::getById($obj->$getter()->getId());

                    $obj->$setter( $res );

                    if ($recursive) { $obj->$setter( $dao::fetch($res, true) ); }
                }
            }
        }

        return $obj;
    }
}
