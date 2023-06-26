<?php

namespace Statamic\Data;

use Closure;
use Illuminate\Support\Collection;
use Statamic\Support\Arr;
use Statamic\Support\Str;

trait StoresScopedComputedFieldCallbacks
{
    protected $computedFieldCallbacks;

    /**
     * @param  string|array  $scopes
     * @param  string  $field
     * @param  Closure  $callback
     */
    public function computed($scopes, string $field, Closure $callback)
    {
        foreach (Arr::wrap($scopes) as $scope) {
            $this->computedFieldCallbacks["$scope.$field"] = $callback;
        }
    }

    public function getComputedCallbacks(string $scope): Collection
    {
        return collect($this->computedFieldCallbacks)
            ->filter(fn ($_, $key) => Str::startsWith($key, "{$scope}."))
            ->keyBy(fn ($_, $key) => Str::after($key, "{$scope}."));
    }
}
