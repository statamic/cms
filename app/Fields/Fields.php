<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Facades\Statamic\Fields\FieldRepository;

class Fields
{
    protected $items;

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

        return $this;
    }

    public function items()
    {
        return $this->items;
    }

    public function all(): Collection
    {
        return $this->items->map(function ($config) {
            return FieldRepository::find($config['field'])
                ->setHandle($config['handle']);
        });
    }

    public function merge($fields)
    {
        $items = $this->items->merge($fields->items());

        return new static($items);
    }

    public function toPublishArray()
    {
        return $this->all()->map->toPublishArray()->all();
    }
}
