<?php
namespace Models\Dao;

use Kernel\Orm\Database;

/**
 * Class Dao
 * @package Models\dao
 */
class Dao
{
    use Database;



    /**
     * Fetch data with id
     * Example :
     * $user = new User(10);
     * $user->fetch(); // Retrieve all data from the user
     * @param bool $recursive
     */
    public function fetch($recursive = false)
    {
        $class = get_class($this);

        if ($this->getId() != null) {
            $reflect = new \ReflectionObject($this);
            $obj = $class::getById($this->getId());

            foreach ($reflect->getProperties() as $property) {
                if (!$property->isStatic()) {

                    $setter = "set" . Utils::toPascalCase($property->getName());
                    $getter = "get" . Utils::toPascalCase($property->getName());

                    // If the value is an object and the recursive mode is true :
                    // Fetch all sub data
                    if ($recursive && is_object($obj->$getter())) {
                        $obj->$getter()->fetch(true);

                    }

                    $this->$setter( $obj->$getter() );

                }
            }
        }

        return $this;
    }
}
