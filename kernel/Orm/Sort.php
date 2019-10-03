<?php
namespace Kernel\Orm;

use AdrienM\Collection\Collection;

class Sort
{
    /**
     * Collection for the order of items
     * @var Collection
     */
    private $rules;

    private const ASCENDING = "ASC";
    private const DESCENDING = "DESC";

    /**
     * Sort constructor.
     * @param Collection $rules
     */
    public function __construct(Collection $rules = null)
    {
        $this->rules = null != $rules ? $rules : new Collection();
    }

    /**
     * To set keys in ascending sort
     * @param string ...$keys
     * @throws \AdrienM\Collection\CollectionException
     * @return self
     */
    public function toAscending(string ...$keys) {
        foreach ($keys as $key) {
            $this->rules->add(self::ASCENDING, $key);
        }

        return $this;
    }

    /**
     * To set keys in descending sort
     * @param string ...$keys
     * @throws \AdrienM\Collection\CollectionException
     * @return self
     */
    public function toDescending(string ...$keys) {
        foreach ($keys as $key) {
            $this->rules->add(self::DESCENDING, $key);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    /**
     * @param Collection $rules
     */
    public function setRules(Collection $rules): void
    {
        $this->rules = $rules;
    }

}
