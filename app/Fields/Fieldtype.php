<?php

namespace Statamic\Fields;

use Statamic\API\Str;
use Illuminate\Contracts\Support\Arrayable;

abstract class Fieldtype implements Arrayable
{
    protected $field;
    protected $handle;
    protected $title;
    protected $localizable = true;
    protected $validatable = true;
    protected $defaultable = true;
    protected $selectable = true;
    protected $categories = ['text'];
    protected $rules = [];
    protected $extraRules = [];
    protected $defaultValue;
    protected $configFields = [];
    protected $icon;

    public function setField(Field $field)
    {
        $this->field = $field;

        return $this;
    }

    public function field(): ?Field
    {
        return $this->field;
    }

    public function handle(): string
    {
        if ($this->handle) {
            return $this->handle;
        }

        $class = (new \ReflectionClass(static::class))->getShortName();

        return Str::removeRight(snake_case($class), '_fieldtype');
    }

    public function title(): string
    {
        return $this->title ?? Str::humanize($this->handle());
    }

    public function localizable(): bool
    {
        return $this->localizable;
    }

    public function validatable(): bool
    {
        return $this->validatable;
    }

    public function defaultable(): bool
    {
        return $this->defaultable;
    }

    public function selectable(): bool
    {
        return $this->selectable;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function rules(): array
    {
        return Validation::explodeRules($this->rules);
    }

    public function extraRules(): array
    {
        return array_map([Validation::class, 'explodeRules'], $this->extraRules);
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'localizable' => $this->localizable(),
            'validatable' => $this->validatable(),
            'defaultable' => $this->defaultable(),
            'selectable'  => $this->selectable(),
            'categories' => $this->categories(),
            'icon' => $this->icon(),
            'config' => $this->configFields()->toPublishArray()
        ];
    }

    public function configFields(): Fields
    {
        $fields = collect($this->configFieldItems())->map(function ($field, $handle) {
            return compact('handle', 'field');
        });

        return new Fields($fields);
    }

    protected function configFieldItems(): array
    {
        return $this->configFields;
    }

    public function icon()
    {
        return $this->icon ?? $this->handle();
    }
}
