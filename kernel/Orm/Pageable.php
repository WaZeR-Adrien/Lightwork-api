<?php
namespace Kernel\Orm;

use AdrienM\Collection\Collection;
use Kernel\Api\ApiErrorCode;
use Kernel\Api\ApiException;

class Pageable
{
    /**
     * Total of data
     * @var int
     */
    private $total;

    /**
     * Length of data per page
     * @var int
     */
    private $lengthPerPage;

    /**
     * Current page
     * @var int
     */
    private $currentPage;

    /**
     * Sort
     * @var Sort
     */
    private $sort;

    /**
     * Data
     * @var Collection
     */
    private $data;

    /**
     * Pageable constructor.
     * @param int|null $lengthPerPage
     * @param int|null $currentPage
     * @param Sort|null $sort
     */
    public function __construct(int $lengthPerPage = null, int $currentPage = null, Sort $sort = null)
    {
        if (null != $lengthPerPage && $lengthPerPage < 1) { throw new ApiException(ApiErrorCode::PAGE001, $lengthPerPage); }
        if (null != $currentPage && $currentPage < 1) { throw new ApiException(ApiErrorCode::PAGE002, $currentPage); }

        $this->lengthPerPage = $lengthPerPage;
        $this->currentPage = $currentPage;
        $this->sort = null != $sort ? $sort : new Sort();
    }

    /**
     * @return int
     */
    public function getTotal(): ?int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getLengthPerPage(): ?int
    {
        return $this->lengthPerPage;
    }

    /**
     * @param int $lengthPerPage
     */
    public function setLengthPerPage(int $lengthPerPage): void
    {
        $this->lengthPerPage = $lengthPerPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): ?int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return Sort
     */
    public function getSort(): Sort
    {
        return $this->sort;
    }

    /**
     * @param Sort $sort
     */
    public function setSort(Sort $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return Collection
     */
    public function getData(): ?Collection
    {
        return $this->data;
    }

    /**
     * @param Collection $data
     */
    public function setData(Collection $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return [
            "total" => $this->total,
            "currentPage" => $this->currentPage,
            "lengthPerPage" => $this->lengthPerPage,
            "sort" => $this->sort->getRules()->getAll(),
            "data" => $this->data->getAll()
        ];
    }
}
