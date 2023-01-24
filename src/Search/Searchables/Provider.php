<?php

namespace Statamic\Search\Searchables;

use Statamic\Facades\Search;
use Statamic\Search\Index;
use Statamic\Search\ProvidesSearchables;

abstract class Provider implements ProvidesSearchables
{
    protected static $handle;
    protected static $referencePrefix;
    protected $index;
    protected $keys;

    public static function register()
    {
        Search::registerSearchableProvider(static::class);
    }

    public static function handle(): string
    {
        if (static::$handle) {
            return static::$handle;
        }

        throw new \Exception('Searchable provider handle not defined.');
    }

    public static function referencePrefix(): string
    {
        if (static::$referencePrefix) {
            return static::$referencePrefix;
        }

        throw new \Exception('Searchable provider referencePrefix not defined.');
    }

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
