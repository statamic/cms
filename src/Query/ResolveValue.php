<?php

namespace Statamic\Query;

use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Query\QueryableValue;
use Statamic\Support\Str;

class ResolveValue
{
    public function __invoke($item, $name)
    {
        $nameExploded = explode('->', $name);

        while (! empty($nameExploded)) {
            $name = array_shift($nameExploded);
            $item = $this->resolveItemPartValue($item, $name);

            if (is_null($item)) {
                return;
            }
        }

        return $item;
    }

    private function resolveItemPartValue($item, $name)
    {
        $value = $this->getItemPartValue($item, $name);

        return $value instanceof QueryableValue
            ? $value->toQueryableValue()
            : $value;
    }

    private function getItemPartValue($item, $name)
    {
        if (is_array($item)) {
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
