<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use Statamic\Facades\Blink;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Support\Str;

use function Statamic\trans as __;

abstract class FormFieldtype implements Arrayable
{
    use HasHandle, RegistersItself {
        handle as protected traitHandle;
    }

    protected static $title;
    protected static $binding = 'form-fields';
    protected static $fieldtype;

    protected $field;
    protected $selectable = true;
    protected $categories = [];
    protected $keywords = [];
    protected $configFields = [];
    protected $icon;

    public function title(): string
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

    public function setField(FormField $field)
    {
        $this->field = clone $field;

        return $this;
    }

    public function field(): ?FormField
    {
        return $this->field;
    }

    public static function handle(): string
    {
        return Str::removeRight(static::traitHandle(), '_form_field');
    }

    public static function fieldtype(): ?string
    {
        return static::$fieldtype;
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

    public function config(?string $key = null, $fallback = null)
    {
        if (! $this->field) {
            return $fallback;
        }

        $config = $this->configFields()->all()
            ->map->defaultValue()
            ->merge($this->field->config());

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
        return $this->configFields;
    }

    public function toField(): Field
    {
        return new Field($this->handle(), $this->toFieldArray());
    }

    abstract public function toFieldArray(): array;

    public function isSelectable(): bool
    {
        if (FormFieldtypeRepository::selectableIsOverriden($this->handle())) {
            return FormFieldtypeRepository::hasBeenMadeSelectable($this->handle());
        }

        return $this->selectable;
    }

    public static function makeSelectable(): void
    {
        FormFieldtypeRepository::makeSelectable(static::handle());
    }

    public static function makeUnselectable(): void
    {
        FormFieldtypeRepository::makeUnselectable(static::handle());
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'categories' => $this->categories(),
            'keywords' => $this->keywords(),
            'icon' => $this->icon(),
            'config' => $this->configFields()->toPublishArray(),
        ];
    }
}
