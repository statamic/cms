<?php

namespace Statamic\Search;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Search\Result as Contract;
use Statamic\Contracts\Search\Searchable;
use Statamic\Data\HasAugmentedInstance;

class Result implements Contract, ContainsQueryableValues
{
    use HasAugmentedInstance {
        toAugmentedCollection as traitToAugmentedCollection;
    }

    protected $searchable;
    protected $type;
    protected $score;
    protected $index;
    protected $result;

    public function __construct(Searchable $searchable, $type)
    {
        $this->searchable = $searchable;
        $this->type = $type;
    }

    public function setRawResult(array $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getRawResult(): array
    {
        return $this->result;
    }

    public function setIndex(Index $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function getIndex(): Index
    {
        return $this->index;
    }

    public function getSearchable(): Searchable
    {
        return $this->searchable;
    }

    public function getReference(): string
    {
        return $this->searchable->getSearchReference();
    }

    public function setScore(int $score = null): self
    {
        $this->score = $score;

        return $this;
    }

    public function getScore(): int
    {
        return (int) $this->score;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getQueryableValue($field)
    {
        if ($field === 'status') {
            return method_exists($this->searchable, 'status') ? $this->searchable->status() : 'published';
        }

        if ($this->searchable instanceof ContainsQueryableValues) {
            return $this->searchable->getQueryableValue($field);
        }

        throw new \Exception('Searchable '.get_class($this->searchable).' must implement '.ContainsQueryableValues::class);
    }

    public function toAugmentedCollection($keys = null)
    {
        return $this->traitToAugmentedCollection($keys)->merge([
            'result_type' => $this->getType(),
            'search_score' => $this->getScore(),
        ])->merge($this->index->extraAugmentedResultData($this));
    }

    public function newAugmentedInstance(): Augmented
    {
        if ($this->searchable instanceof Augmentable) {
            return $this->searchable->newAugmentedInstance();
        }

        throw new \Exception('Searchable '.get_class($this->searchable).' must implement '.Augmentable::class.'.');
    }

    public function getCpTitle(): string
    {
        return $this->searchable->getCpSearchResultTitle();
    }

    public function getCpUrl(): string
    {
        return $this->searchable->getCpSearchResultUrl();
    }

    public function getCpBadge(): string
    {
        return $this->searchable->getCpSearchResultBadge();
    }

    public function get($key, $fallback = null)
    {
        return $this->searchable->get($key, $fallback);
    }

    public function setSupplement($key, $value)
    {
        $this->searchable->setSupplement($key, $value);
    }
}
