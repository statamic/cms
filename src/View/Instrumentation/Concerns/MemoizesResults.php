<?php

namespace Statamic\View\Instrumentation\Concerns;

/** @internal */
trait MemoizesResults
{
    /** @return void */
    protected function flushResultCache()
    {
        $this->configurationRevision++;
        $this->resultCache = [];

        if ($this->cacheScope !== null) {
            unset(static::$sharedResultCache[$this->cacheScope]);
            $this->cacheScope = null;
        }
    }

    /** @return void */
    public static function flushSharedResultCache()
    {
        static::$sharedResultCache = [];
    }

    /**
     * @param  string  $key
     * @return string|null
     */
    protected function cachedResult($key)
    {
        if ($this->cacheScope !== null) {
            return static::$sharedResultCache[$this->cacheScope][$key] ?? null;
        }

        return $this->resultCache[$key] ?? null;
    }

    /**
     * @param  string  $key
     * @param  string  $result
     * @return string
     */
    protected function storeResult($key, $result)
    {
        if ($this->cacheScope !== null) {
            static::$sharedResultCache[$this->cacheScope] = $this->evict(
                static::$sharedResultCache[$this->cacheScope] ?? []
            );

            return static::$sharedResultCache[$this->cacheScope][$key] = $result;
        }

        $this->resultCache = $this->evict($this->resultCache);

        return $this->resultCache[$key] = $result;
    }

    /**
     * @param  array<string, string>  $cache
     * @return array<string, string>
     */
    protected function evict(array $cache)
    {
        if (count($cache) < $this->resultCacheLimit) {
            return $cache;
        }

        return array_slice($cache, intdiv($this->resultCacheLimit, 2), null, true);
    }
}
