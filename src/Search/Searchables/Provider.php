<?php

namespace Statamic\Search\Searchables;

use Statamic\Search\Index;
use Statamic\Search\ProvidesSearchables;

abstract class Provider implements ProvidesSearchables
{
    protected $index;
    protected $keys;

    public function setIndex(Index $index)
    {
        $this->index = $index;

        return $this;
    }

    public function setKeys(array $keys): self
    {
        $this->keys = $keys;

        return $this;
    }

    protected function usesWildcard()
    {
        return in_array('*', $this->keys);
    }

    protected function filter()
    {
        $filter = $this->index->config()['filter'] ?? null;

        if (is_string($filter)) {
            $filter = fn ($item) => app($filter)->handle($item);
        }

        return $filter ?? $this->defaultFilter();
    }

    protected function defaultFilter()
    {
        return fn () => true;
    }
}
