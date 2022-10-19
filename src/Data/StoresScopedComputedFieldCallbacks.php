<?php

namespace Statamic\Data;

use Closure;
use Illuminate\Support\Collection;
use Statamic\Support\Str;

trait StoresScopedComputedFieldCallbacks
{
    protected $computedFieldCallbacks;

    public function computed(string $scope, string $field, Closure $callback)
    {
        $this->computedFieldCallbacks["$scope.$field"] = $callback;
    }

    public function getComputedCallbacks(string $scope): Collection
    {
        return collect($this->computedFieldCallbacks)
            ->filter(fn ($_, $key) => Str::startsWith($key, "{$scope}."))
            ->keyBy(fn ($_, $key) => Str::after($key, "{$scope}."));
    }
}
