<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Search;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Aggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Query\ScoreQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\Struct;

class Criteria extends Struct
{
    /**
     * no total count will be selected. Should be used if no pagination required (fastest)
     */
    public const TOTAL_COUNT_MODE_NONE = 0;
    /**
     * exact total count will be selected. Should be used if an exact pagination is required (slow)
     */
    public const TOTAL_COUNT_MODE_EXACT = 1;
    /**
     * fetches limit * 5 + 1. Should be used if pagination can work with "next page exists" (fast)
     */
    public const TOTAL_COUNT_MODE_NEXT_PAGES = 2;

    /**
     * @var FieldSorting[]
     */
    protected $sorting = [];

    /**
     * @var Filter[]
     */
    protected $filters = [];

    /**
     * @var Filter[]
     */
    protected $postFilters = [];

    /**
     * @var Aggregation[]
     */
    protected $aggregations = [];

    /**
     * @var ScoreQuery[]
     */
    protected $queries = [];

    /**
     * @var int|null
     */
    protected $offset;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int
     */
    protected $totalCountMode = self::TOTAL_COUNT_MODE_NONE;

    /**
     * @var Criteria[]
     */
    protected $associations = [];

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getTotalCountMode(): int
    {
        return $this->totalCountMode;
    }

    /**
     * @return FieldSorting[]
     */
    public function getSorting(): array
    {
        return $this->sorting;
    }

    /**
     * @return Aggregation[]
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return Filter[]
     */
    public function getPostFilters(): array
    {
        return $this->postFilters;
    }

    /**
     * @return ScoreQuery[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    public function getAssociations(): array
    {
        return $this->associations;
    }

    public function getAssociation(string $field): ?Criteria
    {
        return $this->associations[$field] ?? null;
    }

    public function addFilter(Filter ...$queries): self
    {
        foreach ($queries as $query) {
            $this->filters[] = $query;
        }

        return $this;
    }

    public function addSorting(FieldSorting ...$sorting): self
    {
        foreach ($sorting as $sort) {
            $this->sorting[] = $sort;
        }

        return $this;
    }

    public function addAggregation(Aggregation ...$aggregations): self
    {
        foreach ($aggregations as $aggregation) {
            $this->aggregations[] = $aggregation;
        }

        return $this;
    }

    public function addPostFilter(Filter ...$queries): self
    {
        foreach ($queries as $query) {
            $this->postFilters[] = $query;
        }

        return $this;
    }

    public function addQuery(ScoreQuery ...$queries): self
    {
        foreach ($queries as $query) {
            $this->queries[] = $query;
        }

        return $this;
    }

    public function addAssociation(string $field, Criteria $criteria = null): self
    {
        $this->associations[$field] = $criteria ?? new self();

        return $this;
    }

    public function hasAssociation(string $field): bool
    {
        return isset($this->associations[$field]);
    }

    public function resetSorting(): self
    {
        $this->sorting = [];

        return $this;
    }

    public function resetAssociations(): self
    {
        $this->associations = [];

        return $this;
    }

    public function resetQueries(): self
    {
        $this->queries = [];

        return $this;
    }

    public function resetFilters(): self
    {
        $this->filters = [];

        return $this;
    }

    public function resetPostFilters(): self
    {
        $this->postFilters = [];

        return $this;
    }

    public function resetAggregations(): self
    {
        $this->aggregations = [];

        return $this;
    }

    public function setTotalCountMode(int $totalCountMode): self
    {
        $this->totalCountMode = $totalCountMode;

        return $this;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(?int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }
}
