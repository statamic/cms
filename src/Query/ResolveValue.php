<?php

namespace Statamic\Query;

use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Query\QueryableValue;
use Statamic\Support\Str;

class ResolveValue
{
    public function __invoke($item, $name)
    {
        $this->nameExploded = explode('->', $name);

        while (! empty($this->nameExploded)) {
            $name = array_shift($this->nameExploded);
            $item = $this->resolveItemPartValue($item, $name);

            if (is_null($item)) {
                return;
            }
        }

        return $item;
    }

    private function resolveItemPartValue($item, $name, $wildcardDepth = 0)
    {
        $value = $this->getItemPartValue($item, $name, $wildcardDepth);

        return $value instanceof QueryableValue
            ? $value->toQueryableValue()
            : $value;
    }

    private function getItemPartValue($item, $name, $wildcardDepth)
    {
        if (is_array($item)) {
            if ($name === '*' && ! empty($this->nameExploded)) {
                foreach ($item as $value) {
                    if ($this->resolveItemPartValue($value, $this->nameExploded[$wildcardDepth], $wildcardDepth + 1)) {
                        return $value;
                    }
                }
            }

            return $item[$name] ?? null;
        }

        if (is_scalar($item)) {
            return null;
        }

        if ($item instanceof ContainsQueryableValues) {
            return $item->getQueryableValue($name);
        }

        if (method_exists($item, $method = Str::camel($name))) {
            return $item->{$method}();
        }

        if (method_exists($item, 'value')) {
            return $item->value($name);
        }

        return $item->get($name);
    }
}
