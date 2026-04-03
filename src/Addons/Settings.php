<?php

namespace Statamic\Addons;

use Statamic\Contracts\Addons\Settings as Contract;
use Statamic\Contracts\Addons\SettingsRepository;
use Statamic\Events\AddonSettingsSaved;
use Statamic\Events\AddonSettingsSaving;
use Statamic\Facades\Antlers;
use Statamic\Support\Arr;
use Statamic\View\Cascade;

abstract class Settings implements Contract
{
    private Addon $addon;
    private array $settings;
    private array $rawSettings;

    public function __construct(Addon $addon, array $settings = [])
    {
        $this->addon = $addon;
        $this->setValues($settings);
    }

    public function addon(): Addon
    {
        return $this->addon;
    }

    public function all(): array
    {
        return $this->settings;
    }

    public function raw(): array
    {
        return $this->rawSettings;
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->settings, $key, $default);
    }

    public function set(string|array $key, mixed $value = null): self
    {
        return is_array($key) ? $this->setValues($key) : $this->setValue($key, $value);
    }

    private function setValue(string $key, mixed $value): self
    {
        Arr::set($this->rawSettings, $key, $value);
        Arr::set($this->settings, $key, $this->resolveAntlersValue($value));

        return $this;
    }

    private function setValues(array $values): self
    {
        $this->rawSettings = $values;
        $this->settings = $this->resolveAntlers($this->applyBlueprintDefaults($values));

        return $this;
    }

    public function save(): bool
    {
        if (AddonSettingsSaving::dispatch($this) === false) {
            return false;
        }

        app(SettingsRepository::class)->save($this);

        AddonSettingsSaved::dispatch($this);

        return true;
    }

    public function delete(): bool
    {
        return app(SettingsRepository::class)->delete($this);
    }

    private function applyBlueprintDefaults(array $settings): array
    {
        if (! $blueprint = $this->addon->settingsBlueprint()) {
            return $settings;
        }

        $defaults = $blueprint->fields()->all()
            ->mapWithKeys(fn ($field) => [$field->handle() => $field->defaultValue()])
            ->whereNotNull()
            ->all();

        return array_merge($defaults, $settings);
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

        return (string) Antlers::parse($value, ['config' => Cascade::config()]);
    }
}
