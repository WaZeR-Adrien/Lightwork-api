<?php
namespace Kernel\Tools\Collection;

use function Jasny\object_get_properties;
use Kernel\Tools\Utils;

class Collection
{
    /**
     * Items of the collection
     * @var array
     */
    private $items = [];

    private $alias = [
        "count" => "length", "all" => "getAll", "first" => "getFirst", "last" => "getLast"
    ];

    /**
     * Collection constructor.
     * @param array $firstitems
     */
    public function __construct($firstItems = [])
    {
        $this->items = $firstItems;
    }


    /**
     * Check if the collection is empty
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Get the number of items
     * @return int
     */
    public function length()
    {
        return count($this->items);
    }

    /**
     * Get the sum of values
     * @param null $key
     * @return int
     */
    public function sum($key = null)
    {
        $sum = 0;

        foreach ($this->items as $item) {

            if (is_array($item) && null != $key) {
                foreach ($item as $subKey => $subItem) {

                    if ($subKey == $key && is_int($subItem)) { $sum += $subItem; }

                }
            } elseif (is_int($item)) {
                $sum += $item;
            }

        }

        return $sum;
    }

    /**
     * Check if the collection contains the value
     * @param $value
     * @return bool
     */
    public function contains($value)
    {
        return in_array($value, $this->items);
    }

    /**
     * Check if the key exists in the collection
     * @param $key
     * @return bool
     */
    public function keyExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get all keys
     * @return array
     */
    public function keys()
    {
        return array_keys($this->items);
    }

    /**
     * Add a new value in the collection with or without key
     * @param mixed $value
     * @param string|null $key
     * @return Collection
     * @throws KeyAlreadyUseException
     */
    public function add($value, $key = null)
    {
        if (null != $key) {

            if ($this->keyExists($key, $this->items)) {
                throw new KeyAlreadyUseException("Key $key already in use.");
            } else {
                $this->items[$key] = $value;
            }

        } else {
            $this->items[] = $value;
        }

        return $this;
    }

    /**
     * Push items of the collection in this collection
     * Keep the old values if there are the same key in collections
     * @param Collection $collection
     * @return Collection
     */
    public function push(Collection $collection)
    {
        foreach ($collection->getAll() as $k => $v) {
            if (!$this->keyExists($k)) {
                $this->items[$k] = $v;
            }
        }

        return $this;
    }

    /**
     * Merge items of the collection with this collection
     * Replace the old values by the new values if there are the same key in collections
     * @param Collection $collection
     * @return Collection
     */
    public function merge(Collection $collection)
    {
        foreach ($collection->getAll() as $key => $value) {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * Add a new value in the collection with or without key
     * @param string $key
     * @param mixed $value
     * @return Collection
     * @throws KeyAlreadyUseException
     */
    public function update($key, $value)
    {
        if ($this->keyExists($key, $this->items)) {
            $this->items[$key] = $value;
        } else {
            throw new KeyAlreadyUseException("The key $key does not exist in the collection.");
        }

        return $this;
    }

    /**
     * Get value of the collection with the key
     * @param string|int $key
     * @return mixed
     * @throws KeyAlreadyUseException
     */
    public function get($key)
    {
        if ($this->keyExists($key, $this->items)) {
            return $this->items[$key];
        } else {
            throw new KeyAlreadyUseException("The key $key does not exist in the collection.");
        }
    }

    /**
     * Get all items of the collection
     * @return array
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     * Get the first item of the collection
     * @return mixed|null
     */
    public function getFirst()
    {
        if (empty($this->items)) { return null; }

        foreach ($this->items as $item) {
            return $item;
        }
    }

    /**
     * Get the last item of the collection
     * @return mixed
     */
    public function getLast()
    {
        return !empty($this->items) ? end($this->items) : null;
    }

    /**
     * Drop value by key or directly by value
     * @param mixed $keyOrValue
     * @return Collection
     * @throws KeyAlreadyUseException
     */
    public function drop($keyOrValue)
    {
        if ($this->keyExists($keyOrValue, $this->items)) {
            unset($this->items[$keyOrValue]);
        } elseif (in_array($keyOrValue, $this->items)) {

            foreach ($this->items as $key => $value) {
                if ($value == $keyOrValue) {
                    unset($this->items[$key]);
                }
            }

        } else {
            throw new KeyAlreadyUseException("The key $keyOrValue does not exist in the collection.");
        }

        return $this;
    }

    /**
     * Erase a part of the collection
     * @param int $start
     * @param null|int $length
     * @return $this
     */
    public function slice($start, $length = null)
    {
        array_slice($this->items, $start, $length);

        return $this;
    }

    /**
     * Reset collection
     * @return Collection
     */
    public function purge()
    {
        $this->items = [];

        return $this;
    }

    /**
     * Reverse the collection items
     * @return Collection
     */
    public function reverse()
    {
        return new self(array_reverse($this->items, true));
    }

    /**
     * Convert all objects to arrays with the getters
     * @return array
     */
    public function toArrays()
    {
        foreach ($this->items as $k => &$v) {
            // If the value is an object :
            // Get all values of the object with the getters and save in an array
            if (is_object($v)) {

                $array = [];
                $reflect = new \ReflectionObject($v);
                foreach ($reflect->getProperties() as $property) {
                    $propertyName = $property->getName();

                    $getter = "get" . Utils::toPascalCase($propertyName);
                    $array[$propertyName] = $v->$getter();
                }
                $v = $array;
                if ($k === 'user') {
                    //var_dump($v);
                }
            }

            // If the value is an array :
            // Load this function to convert the sub objects in arrays...
            if (is_array($v)) {
                $collection = new Collection($v);
                $v = $collection->toArrays();
            }
        }

        return $this->items;
    }

    /**
     * @param callable $callback
     * @return Collection
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new self(array_combine($keys, $items));
    }

    /**
     * Call a method with the alias
     * @param $name
     * @param $args
     * @return mixed
     * @throws MethodDoesNotExistsException
     */
    public function __call($name, $args)
    {
        if (array_key_exists($name, $this->alias)) {
            return call_user_func_array([$this, $this->alias[$name]], $args);
        }

        throw new MethodDoesNotExistsException("Method or alias $name does not exist.");
    }
}
