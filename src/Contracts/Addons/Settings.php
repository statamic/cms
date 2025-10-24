<?php

namespace Statamic\Contracts\Addons;

use Statamic\Addons\Addon;

interface Settings
{
    public function addon(): Addon;

    public function all(): array;

    public function raw(): array;

    public function get(string $key, $default = null);

    public function set(string $key, $value): self;

    public function save(): bool;

    public function delete(): bool;
}
