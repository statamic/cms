<?php

namespace Statamic\Fields;

use Statamic\API\Str;

abstract class Fieldtype
{
    protected $field;
    protected $handle;
    protected $title;
    protected $localizable = true;
    protected $validatable = true;
    protected $defaultable = true;
    protected $selectable = true;
    protected $categories = ['text'];

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

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'localizable' => $this->localizable(),
            'validatable' => $this->validatable(),
            'defaultable' => $this->defaultable(),
            'selectable'  => $this->selectable(),
            'categories' => $this->categories(),
        ];
    }
}
