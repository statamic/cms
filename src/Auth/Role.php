<?php

namespace Statamic\Auth;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades;

abstract class Role implements RoleContract, Augmentable, ArrayAccess, Arrayable
{
    use ContainsData, HasAugmentedData;

    public function __construct()
    {
        $this->data = collect();
    }

    public function editUrl()
    {
        return cp_route('roles.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('roles.destroy', $this->handle());
    }

    public function updateUrl()
    {
        return cp_route('roles.update', $this->handle());
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Role::{$method}(...$parameters);
    }

    public function augmentedArrayData()
    {
        return $this->data()->merge([
            'title' => $this->title(),
            'handle' => $this->handle(),
        ])->all();
    }

    /**
      * Get or set the blueprint.
      *
      * @param string|null|bool
      * @return \Statamic\Fields\Blueprint
      */
    public function blueprint()
    {
        return Facades\Role::blueprint();
    }
}
