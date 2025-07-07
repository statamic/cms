<?php

namespace Statamic\Extend;

use Illuminate\Support\Collection;
use Statamic\Contracts\Extend\AddonSettings as Contract;
use Statamic\Contracts\Extend\AddonSettingsRepository;

abstract class AbstractAddonSettings implements Contract
{
    protected Addon $addon;
    protected Collection $settings;

    public function __construct(Addon $addon, array $settings = [])
    {
        $this->addon = $addon;
        $this->settings = collect($settings);
    }

    public function addon(): Addon
    {
        return $this->addon;
    }

    public function values($values = null): Collection|self
    {
        if (func_num_args() === 0) {
            return $this->settings;
        }

        $this->settings = collect($values);

        return $this;
    }

    public function get(string $key, $default = null)
    {
        return $this->settings->get($key, $default);
    }

    public function has(string $key): bool
    {
        return $this->settings->has($key);
    }

    public function set(string $key, $value): self
    {
        $this->settings->put($key, $value);

        return $this;
    }

    public function merge(array $settings): self
    {
        $this->settings = $this->settings->merge($settings);

        return $this;
    }

    public function save(): bool
    {
        return app(AddonSettingsRepository::class)->save($this);
    }

    public function delete(): bool
    {
        return app(AddonSettingsRepository::class)->delete($this);
    }
}
