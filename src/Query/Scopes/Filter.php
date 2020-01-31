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

    public function required()
    {
        return false;
    }

    public function level1()
    {
        return false;
    }

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

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'extra' => $this->extra(),
            'required' => $this->required(),
            'level1' => $this->level1(),
            'fields' => $this->fields()->toPublishArray(),
            'meta' => $this->fields()->meta(),
            'values' => $this->fields()->all()->map->defaultValue(),
        ];
    }
}
