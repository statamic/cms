<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Facades\Statamic\Fields\FieldRepository;

class Fields
{
    protected $items;
    protected $fields;

    public function __construct($items = [])
    {
        $this->setItems($items);
    }

    public function setItems($items)
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        $this->items = collect($items);

        $this->fields = $this->items->map(function ($config) {
            return $this->field($config)->setHandle($config['handle']);
        });

        return $this;
    }

    public function items()
    {
        return $this->items;
    }

    public function all(): Collection
    {
        return $this->fields;
    }

    public function merge($fields)
    {
        $items = $this->items->merge($fields->items());

        return new static($items);
    }

    public function toPublishArray()
    {
        return $this->fields->values()->map->toPublishArray()->all();
    }

    public function addValues(array $values)
    {
        $this->fields->each(function ($field) use ($values) {
            return $field->setValue(array_get($values, $field->handle()));
        });

        return $this;
    }

    public function values()
    {
        return $this->fields->mapWithKeys(function ($field) {
            return [$field->handle() => $field->value()];
        })->all();
    }

    public function process()
    {
        $this->fields->each->process();

        return $this;
    }

    public function preProcess()
    {
        $this->fields->each->preProcess();

        return $this;
    }

    protected function field(array $config): Field
    {
        // If "field" is a string, it's a reference to a field in a fieldset.
        if (is_string($config['field'])) {
            if ($field = FieldRepository::find($config['field'])) {
                return $field;
            }

            throw new \Exception("Field {$config['field']} not found.");
        }

        // Otherwise, the field has been configured inline.
        return new Field($config['handle'], $config['field']);
    }
}
