<?php

namespace Models;

use Kernel\Orm\Database;
use Kernel\Tools\Utils;

/**
 * Class SupportCategory
 * @package Models
 */
class Entity
{
	use Database;


	/**
     * Convert the object to a array and the sub objects to sub arrays
     * @return array
	 */
	public function toArray()
	{
		$array = [];

        $reflect = new \ReflectionObject($this);

        foreach ($reflect->getProperties() as $property) {
            if (!$property->isStatic()) {

                $getter = "get" . Utils::toPascalCase($property->getName());

                $value = $this->$getter();

                // If the value is an object :
                // Load this function to convert the sub objects in arrays...
                if (is_object($value)) {
                    $value = $value->toArray();
                }

                $array[$property->getName()] = $value;

            }
		}

		return $array;
	}

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
