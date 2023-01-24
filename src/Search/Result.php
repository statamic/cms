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

    public function __construct(Searchable $searchable, $type)
    {
        $this->searchable = $searchable;
        $this->type = $type;
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

        throw new \Exception('Searchable must implement ContainsQueryableValues.');
    }

    public function toAugmentedCollection($keys = null)
    {
        return $this->traitToAugmentedCollection($keys)->merge([
            'result_type' => $this->getType(),
            'search_score' => $this->getScore(),
        ]);
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
        return $this->searchable->title;
    }

    public function getCpUrl(): string
    {
        return $this->searchable->editUrl();
    }

    public function getCpBadge(): string
    {
        return $this->searchable->getCpSearchResultBadge();
    }

    public function supplement($key, $value)
    {
        $this->searchable->setSupplement($key, $value);
    }
}
