<?php

namespace Statamic\Data;

use Closure;
use Illuminate\Support\Collection;
use Statamic\Facades\Blink;
use Statamic\Support\Arr;
use Statamic\Support\Str;

trait StoresScopedComputedFieldCallbacks
{
    protected $computedFieldCallbacks;

    /**
     * @param  string|array  $scopes
     * @param  string|array $field
     */
    public function computed($scopes, $field, ?Closure $callback = null)
    {
        foreach (Arr::wrap($scopes) as $scope) {
            if (is_array($field)) {
                foreach ($field as $field_name => $field_callback) {
                    $this->computedFieldCallbacks["$scope.$field_name"] = $field_callback;
                }

                continue;
            }

            $this->computedFieldCallbacks["$scope.$field"] = $callback;
        }
    }


    public function getComputedCallbacks(string $scope): Collection
    {
        return Blink::once(__CLASS__.'::getComputedCallbacks'.$scope, function () use ($scope) {
            return collect($this->computedFieldCallbacks)
                ->filter(fn ($_, $key) => Str::startsWith($key, "{$scope}."))
                ->keyBy(fn ($_, $key) => Str::after($key, "{$scope}."));
        });
    }
}
