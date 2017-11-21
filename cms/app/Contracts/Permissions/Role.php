<?php

namespace Statamic\Contracts\Permissions;

use Statamic\Contracts\CP\Editable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

interface Role extends Jsonable, Arrayable, Editable
{
    /**
     * @param string|null $uuid
     * @return string
     */
    public function uuid($uuid = null);

    /**
     * @param string|null $title
     * @return string
     */
    public function title($title = null);

    /**
     * @param string|null $slug
     * @return string
     */
    public function slug($slug = null);

    /**
     * @param array|null $permissions
     * @return \Illuminate\Support\Collection
     */
    public function permissions($permissions = null);

    /**
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission);

    /**
     * @param string $permission
     * @return mixed
     */
    public function addPermission($permission);

    /**
     * @param string $permission
     * @return mixed
     */
    public function removePermission($permission);

    /**
     * @return bool
     */
    public function isSuper();

    /**
     * @return mixed
     */
    public function save();

    /**
     * @return mixed
     */
    public function delete();
}
