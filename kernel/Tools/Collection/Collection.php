<?php
namespace Kernel\Tools;

class Collection
{
    /**
     * Items of the collection
     * @var array
     */
    private $items = [];

    /**
     * Collection constructor.
     * @param array $firstitems
     */
    public function __construct($firstitems = [])
    {
        $this->items = $firstitems;
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
     * Check if the collection contains a value for this key
     * Or directly the value without specific key
     * @param mixed $keyOrValue
     * @return bool
     */
    public function contains($keyOrValue)
    {
        return !empty($this->items[$keyOrValue]) || in_array($keyOrValue, $this->items);
    }

    public function keyExists($key)
    {
        return isset($this->items[$key]);
    }

    /**
     * Add a new value in the collection with or without key
     * @param string|null $key
     * @param mixed $value
     * @return Collection
     */
    public function add($value, $key = null)
    {
        if (null != $key) {

            if (isset($this->items[$key])) {
                throw new CollectionException("Key $key already in use.");
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
        foreach ($collection->getAll() as $key => $value) {
            if (isset($this->items[$key])) {
                $this->items[$key] = $value;
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
     * Add a new value in the collection with or withour key
     * @param string $key
     * @param mixed $value
     * @return Collection
     */
    public function update($key, $value)
    {
        if (array_key_exists($key, $this->items)) {
            $this->items[$key] = $value;
        } else {
            throw new CollectionException("The key $key does not exist in the collection");
        }

        return $this;
    }

    /**
     * Get value by key
     * @param string|int $key
     * @return mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        } else {
            throw new CollectionException("The key $key does not exist in the collection");
        }
    }

    /**
     * Get all items
     * @return array
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     * Drop value by key or directly by value
     * @param mixed $keyOrValue
     * @return Collection
     */
    public function drop($keyOrValue)
    {
        if (array_key_exists($keyOrValue, $this->items)) {
            unset($this->items[$keyOrValue]);
        } elseif (in_array($keyOrValue, $this->items)) {

            foreach ($this->items as $key => $value) {
                if ($value == $keyOrValue) {
                    unset($this->items[$key]);
                }
            }

        } else {
            throw new CollectionException("The key $keyOrValue does not exist in the collection");
        }

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
     * Get all keys
     * @return array
     */
    public function keys()
    {
        return array_keys($this->items);
    }
}
