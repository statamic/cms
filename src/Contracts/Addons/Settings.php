<?php

namespace Statamic\Contracts\Addons;

use Statamic\Addons\Addon;

interface Settings
{
    public function addon(): Addon;

    public function values($values = null): array|self;

    public function raw(): array;

    public function get(string $key, $default = null);

    public function has(string $key): bool;

    public function set(string $key, $value): self;

    public function save(): bool;

    public function delete(): bool;
}
