<?php

namespace Statamic\Query\Scopes;

use Statamic\Fields\Fields;
use Statamic\Extend\HasTitle;
use Statamic\Query\Scopes\Scope;
use Statamic\Extend\RegistersItself;
use Illuminate\Contracts\Support\Arrayable;

abstract class Filter extends Scope implements Arrayable
{
    use HasTitle;

    protected $context = [];
    protected $field;
    protected $fields = [];
    protected $required = false;
    protected $pinned = false;

    public function visibleTo($key)
    {
        return true;
    }

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    public function extra()
    {
        return [];
    }

    public function fields()
    {
        $fields = collect($this->fieldItems())->map(function ($field, $handle) {
            return compact('handle', 'field');
        });

        return new Fields($fields);
    }

    protected function fieldItems()
    {
        if ($this->fields) {
            return $this->fields;
        }

        $field = $this->field ?? ['type' => 'text', 'display' => static::title()];

        return ['value' => $field];
    }

    public function badge($values)
    {
        return collect($values)->first();
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'extra' => $this->extra(),
            'required' => $this->required(),
            'pinned' => $this->pinned(),
            'fields' => $this->fields()->toPublishArray(),
            'meta' => $this->fields()->meta(),
            'values' => $this->fields()->all()->map->defaultValue(),
        ];
    }

    public function __call($method, $args)
    {
        return $this->{$method};
    }
}
