<?php

namespace Statamic\Contracts\Addons;

use Statamic\Addons\Addon;

interface SettingsRepository
{
    public function make(Addon $addon, array $settings = []): Settings;

    public function find(string $addon): ?Settings;

    public function save(Settings $settings): bool;

    public function delete(Settings $settings): bool;
}
