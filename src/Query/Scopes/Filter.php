<?php

namespace Statamic\Query\Scopes;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Extend\HasFields;
use Statamic\Extend\HasTitle;

abstract class Filter extends Scope implements Arrayable
{
    use HasTitle, HasFields;

    protected $context = [];
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

    protected function fieldItems()
    {
        return [
            'value' => [
                'type' => 'text',
            ],
        ];
    }

    public function autoApply()
    {
        return [];
    }

    public function badge($values)
    {
        $valuesSummary = collect($values)
            ->filter()
            ->implode(', ');

        return strtolower($this->title()).': '.$valuesSummary;
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'extra' => $this->extra(),
            'pinned' => $this->pinned(),
            'auto_apply' => $this->autoApply(),
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
