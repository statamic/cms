<?php

namespace Statamic\Contracts\Addons;

use Statamic\Addons\Addon;

interface AddonSettingsRepository
{
    public function make(Addon $addon, array $settings = []): AddonSettings;

    public function find(string $addon): ?AddonSettings;

    public function save(AddonSettings $settings): bool;

    public function delete(AddonSettings $settings): bool;
}
