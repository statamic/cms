<?php

namespace Statamic\Data;

use Closure;
use Exception;
use Statamic\Support\Str;

trait StoresComputedFieldCallbacks
{
    protected $computedFieldCallbacks;

    public function computed(...$args)
    {
        $numArgsRequired = isset($this->scopeComputedFieldCallbacks) && $this->scopeComputedFieldCallbacks
            ? 3
            : 2;

        if (func_num_args() !== $numArgsRequired) {
            throw new Exception("Number of arguments required: {$numArgsRequired}");
        }

        func_num_args() === 3
            ? $this->setScopedComputedCallback(...$args)
            : $this->setComputedCallback(...$args);
    }

    private function setComputedCallback(string $field, Closure $callback)
    {
        $this->computedFieldCallbacks[$field] = $callback;
    }

    private function setScopedComputedCallback(string $scope, string $field, Closure $callback)
    {
        $this->computedFieldCallbacks["$scope.$field"] = $callback;
    }

    public function getComputedCallbacks($fieldPrefix = null)
    {
        return collect($this->computedFieldCallbacks)
            ->filter(function ($callback, $fieldPath) use ($fieldPrefix) {
                return $fieldPrefix
                    ? Str::startsWith($fieldPath, Str::ensureRight((string) $fieldPrefix, '.'))
                    : true;
            })
            ->keyBy(function ($callback, $fieldPath) {
                return collect(explode('.', $fieldPath))->last();
            });
    }
}
