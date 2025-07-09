<?php

namespace Statamic\Extend;

use Statamic\Contracts\Extend\AddonSettings as Contract;
use Statamic\Contracts\Extend\AddonSettingsRepository;
use Statamic\Events\AddonSettingsSaved;
use Statamic\Events\AddonSettingsSaving;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;

abstract class AbstractAddonSettings implements Contract
{
    protected Addon $addon;
    protected array $settings;
    protected array $rawSettings;

    public function __construct(Addon $addon, array $settings = [])
    {
        $this->addon = $addon;
        $this->settings = $this->resolveAntlers($settings);
        $this->rawSettings = $settings;
    }

    public function addon(): Addon
    {
        return $this->addon;
    }

    public function values($values = null): array|self
    {
        if (func_num_args() === 0) {
            return $this->settings;
        }

        $this->rawSettings = $values;
        $this->settings = $this->resolveAntlers($values);

        return $this;
    }

    public function rawValues(): array
    {
        return $this->rawSettings;
    }

    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->settings[$key]);
    }

    public function set(string $key, $value): self
    {
        $this->settings[$key] = $this->resolveAntlersValue($value);
        $this->rawSettings[$key] = $value;

        return $this;
    }

    public function merge(array $settings): self
    {
        $this->rawSettings = array_merge($this->rawSettings, $settings);
        $this->settings = $this->resolveAntlers($this->rawSettings);

        return $this;
    }

    public function save(): bool
    {
        if (AddonSettingsSaving::dispatch($this) === false) {
            return false;
        }

        app(AddonSettingsRepository::class)->save($this);

        AddonSettingsSaved::dispatch($this);

        return true;
    }

    public function delete(): bool
    {
        return app(AddonSettingsRepository::class)->delete($this);
    }

    public function resolveAntlers($config)
    {
        return collect($config)
            ->map(fn ($value) => $this->resolveAntlersValue($value))
            ->all();
    }

    protected function resolveAntlersValue($value)
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($element) => $this->resolveAntlersValue($element))
                ->all();
        }

        return (string) app(RuntimeParser::class)->parse($value, ['config' => config()->all()]);
    }
}
