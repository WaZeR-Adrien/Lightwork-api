<?php

namespace Models;

use Controllers\Docs;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\DescriptionTag;
use AdrienM\Logger\Logger;
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
    public function foreignKeyToObject(): void
    {
        foreach ($this as $k => $v) {
            if (strpos($k, '_id')) {
                $attr = substr($k, 0, strlen($k) - 3);
                $upperAttr = Utils::toPascalCase($attr);

                /**
                 * @var Collection $schemas
                 */
                $schemas = static::getSchemas();


                if ($schemas->keyExists($attr)) {
                    $class = $schemas->get($attr)["model"];
                } else {
                    $class = "\\Models\\" . $upperAttr;
                }

                $obj = new $class($v);

                $setter = "set$upperAttr";
                $this->$setter($obj);

                unset($this->$k);
            }
        }
    }

	/**
     * Convert the object to a array and the sub objects to sub arrays
     * @return array
	 */
	public function toArray(): array
	{
		$array = [];

        foreach ($this->getProperties()->getAll() as $property) {
            $getter = "get" . Utils::toPascalCase($property);

            if (method_exists($this, $getter)) {
                $value = $this->$getter();
            } else {
                $value = $this->$property;
            }

            // If the value is an object :
            // Load this function to convert the sub objects in arrays...
            if (is_object($value)) {
                $value = $value->toArray();
            }

            $array[$property] = $value;
        }

		return $array;
	}

}
