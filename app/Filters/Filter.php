<?php

namespace Statamic\Filters;

use Statamic\Fields\Fields;
use Statamic\Extend\HasTitle;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use Illuminate\Contracts\Support\Arrayable;

abstract class Filter implements Arrayable
{
    use HasTitle, HasHandle, RegistersItself;

    protected static $binding = 'filters';

    protected $context = [];
    protected $field;
    protected $fields = [];

    public function required()
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
        if ($this->field) {
            return ['value' => $this->field];
        }

        return $this->fields;
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'extra' => $this->extra(),
            'required' => $this->required(),
            'fields' => $this->fields()->toPublishArray(),
            'meta' => $this->fields()->meta(),
        ];
    }
}
