<?php

namespace Statamic\Contracts\Permissions;

use Illuminate\Support\Collection;

interface Role
{
    public function title(string $title = null);
    public function handle(string $handle = null);
    public function permissions(): Collection;
    public function hasPermission(string $permission): bool;
    public function addPermission($permission);
    public function removePermission($permission);
    public function isSuper(): bool;
    public function save();
    public function delete();
}
