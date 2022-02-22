<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades;
use Statamic\Facades\Compare;
use Statamic\Fields\Value;

abstract class Role implements RoleContract, Augmentable
{
    use HasAugmentedData;

    public function editUrl()
    {
        return cp_route('roles.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('roles.destroy', $this->handle());
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Role::{$method}(...$parameters);
    }

    public function augmentedArrayData()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
        ];
    }

    public function __get($key)
    {
        $value = $this->augmentedValue($key);

        $value = $value instanceof Value ? $value->value() : $value;

        if (Compare::isQueryBuilder($value)) {
            $value = $value->get();
        }

        return $value;
    }
}
