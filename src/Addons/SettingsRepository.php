<?php

namespace Statamic\Addons;

use Statamic\Contracts\Addons\Settings as AddonSettingsContract;
use Statamic\Contracts\Addons\SettingsRepository as Contract;

abstract class SettingsRepository implements Contract
{
    public function make(Addon $addon, array $settings = []): AddonSettingsContract
    {
        return app()->makeWith(AddonSettingsContract::class, compact('addon', 'settings'));
    }
}
