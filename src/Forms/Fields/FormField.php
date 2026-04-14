<?php

namespace Statamic\Forms\Fields;

use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use Statamic\Facades\Blink;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Support\Str;

use function Statamic\trans as __;

abstract class FormField
{
    use HasHandle, RegistersItself {
        handle as protected traitHandle;
    }

    protected static $title;
    protected static $binding = 'form-fields';

    protected $config;
    protected $categories = [];
    protected $keywords = [];
    protected $icon;

    public static function title(): string
    {
        if (static::$title) {
            return __(static::$title);
        }

        $translation = __($key = 'statamic::form-fields.'.static::handle().'.title');

        if ($translation !== $key) {
            return $translation;
        }

        return __(Str::title(Str::humanize(static::handle())));
    }

    public static function handle(): string
    {
        return Str::removeRight(static::traitHandle(), '_form_field');
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function keywords(): array
    {
        return $this->keywords;
    }

    public function icon(): string
    {
        return $this->icon ?? "form-field-{$this->handle()}";
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function config(?string $key = null, $fallback = null)
    {
        $config = $this->configFields()->all()
            ->map->defaultValue()
            ->merge($this->config);

        return $key
            ? ($config->get($key) ?? $fallback)
            : $config->all();
    }

    public function configFields(): Fields
    {
        if ($cached = Blink::get($blink = 'form-config-fields-'.$this->handle())) {
            return $cached;
        }

        $fields = collect($this->configFieldItems());

        $fields = $fields
            ->map(function ($field, $handle) {
                return compact('handle', 'field');
            });

        $fields = new ConfigFields($fields);

        Blink::put($blink, $fields);

        return $fields;
    }

    protected function configFieldItems(): array
    {
        return [];
    }

    public function toField(): Field
    {
        // todo: not sure this is the right handle
        return new Field($this->handle(), $this->toFieldArray());
    }

    abstract public function toFieldArray(): array;
}
