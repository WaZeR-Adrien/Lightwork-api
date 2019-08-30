<?php

namespace Models;

use Controllers\Docs;
use Kernel\Tools\Utils;

/**
 * Class Entity
 * @package Models
 */
class Entity
{
    /**
     * Convert all foreign key to object
     */
    public function foreignKeyToObject()
    {
        foreach ($this as $k => $v) {
            if (strpos($k, '_id')) {
                $attr = substr($k, 0, strlen($k) - 3);
                $attr = Utils::toPascalCase($attr);

                $annotations = Docs::getPhpDoc($this, "get$attr");

                if (!empty($annotations["return"])) {
                    $class = '\Models\\' . $annotations["return"]["type"];
                } else {
                    $class = '\Models\\' . $attr;
                }

                $class = new $class($v);

                $setter = "set$attr";
                $this->$setter($class);

                unset($this->$k);
            }
        }
    }

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

}
