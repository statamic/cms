<?php

namespace Statamic\Forms\Insights;

use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Summary\FieldResponses;

abstract class Insight
{
    use HasHandle, HasTitle, RegistersItself;

    protected ?string $component = null;
    protected array $defaults = [];
    private array $config = [];

    protected function defaults(): array
    {
        return $this->defaults;
    }

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function config(?string $key = null, mixed $default = null): mixed
    {
        $config = [...$this->defaults(), ...$this->config];

        return $key === null ? $config : ($config[$key] ?? $default);
    }

    public function component(): string
    {
        return $this->component ?? str_replace('_', '-', static::handle()).'-insight';
    }

    /** @return list<FormValueType> */
    abstract public function supports(): array;

    public function appliesTo(FormField $field): bool
    {
        return in_array($field->fieldtype()->valueType(), $this->supports(), true);
    }

    abstract public function props(FieldResponses $responses): array;
}
