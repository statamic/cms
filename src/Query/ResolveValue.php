<?php

namespace Statamic\Query;

use Statamic\Support\Str;

class ResolveValue
{
    public function __invoke($item, $name)
    {
        $nameExploded = explode('->', $name);

        while (! empty($nameExploded)) {
            $name = array_shift($nameExploded);
            $item = $this->getItemPartValue($item, $name);

            if (is_null($item)) {
                return;
            }
        }

        return $item;
    }

    private function getItemPartValue($item, $name)
    {
        if (is_array($item)) {
            return $item[$name] ?? null;
        }

        if (is_scalar($item)) {
            return null;
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
