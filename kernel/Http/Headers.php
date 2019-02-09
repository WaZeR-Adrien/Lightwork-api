<?php
namespace Kernel\Http;

class Headers
{
    /**
     * Headers data
     * @var array
     */
    private $data = [];

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getByKey($key)
    {
        return $this->data[$key];
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }
}