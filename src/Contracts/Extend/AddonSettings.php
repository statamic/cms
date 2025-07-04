<?php

namespace Statamic\Contracts\Extend;

use Illuminate\Support\Collection;
use Statamic\Extend\Addon;

interface AddonSettings
{
    public function addon(): Addon;

    public function values(): Collection;

    public function get(string $key, $default = null);

    public function has(string $key): bool;

    public function set(string $key, $value): self;

    public function merge(array $settings): self;

    public function save(): bool;

    public function delete(): bool;
}
