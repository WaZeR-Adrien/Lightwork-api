<?php
namespace Kernel\Tools;

class Collection
{
    /**
     * Data of the collection
     * @var array
     */
    private $data = [];

    /**
     * Check if the collection is empty
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Get the number of data
     * @return int
     */
    public function length()
    {
        return count($this->data);
    }

    /**
     * Check if the collection contains a value for this key
     * Or directly the value without specific key
     * @param mixed $keyOrValue
     * @return bool
     */
    public function contains($keyOrValue)
    {
        return !empty($this->data[$keyOrValue]) || in_array($keyOrValue, $this->data);
    }

    /**
     * Add a new value in the collection with or without key
     * @param string $key
     * @param mixed $value
     * @return Collection
     */
    public function add($key, $value)
    {
        if (!empty($key)) {
            $this->data[$key] = $value;
        } else {
            $this->data[] = $value;
        }

        return $this;
    }

    /**
     * Push data of the collection in this collection
     * @param Collection $collection
     * @return Collection
     */
    public function push(Collection $collection)
    {
        foreach ($collection->getAll() as $key => $value) {

            $this->data[$key] = $value;

        }

        return $this;
    }

    /**
     * Merge data of the collection with this collection and return the merge
     * @param Collection $collection
     * @return Collection
     */
    public function merge(Collection $collection)
    {
        $merge = new self();

        $merge->push($this);
        $merge->push($collection);

        return $merge;
    }

    /**
     * Add a new value in the collection with or withour key
     * @param string $key
     * @param mixed $value
     * @return Collection
     */
    public function update($key, $value)
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        } else {
            throw new \http\Exception\InvalidArgumentException("The key does not exist in the collection");
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
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            throw new \http\Exception\InvalidArgumentException("The key does not exist in the collection");
        }
    }

    /**
     * Get all data
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * Drop value by key or directly by value
     * @param mixed $keyOrValue
     * @return Collection
     */
    public function drop($keyOrValue)
    {
        if (array_key_exists($keyOrValue, $this->data)) {
            unset($this->data[$keyOrValue]);
        } elseif (in_array($keyOrValue, $this->data)) {

            foreach ($this->data as $key => $value) {
                if ($value == $keyOrValue) {
                    unset($this->data[$key]);
                }
            }

        }

        return $this;
    }

    /**
     * Reset collection
     * @return Collection
     */
    public function purge()
    {
        $this->data = [];

        return $this;
    }
}