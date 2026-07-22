<?php

namespace Statamic\Forms\Connections;

use Closure;
use Statamic\Contracts\Forms\Form;
use Statamic\Statamic;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Statamic\Support\VueComponent;

class Connection
{
    use FluentlyGetsAndSets;

    protected $handle;
    protected $title;
    protected $description;
    protected $icon;
    protected $developer;
    protected $count;
    protected $component;
    protected $componentData;
    protected $routes;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')->getter(function ($title) {
            return $title ?? Str::title($this->handle());
        })->args(func_get_args());
    }

    public function description($description = null)
    {
        return $this->fluentlyGetOrSet('description')->args(func_get_args());
    }

    public function icon($icon = null)
    {
        return $this
            ->fluentlyGetOrSet('icon')
            ->setter(function ($value) {
                return Str::startsWith($value, '<svg') ? $value : Statamic::svg('icons/'.$value);
            })
            ->value($icon);
    }

    public function developer($developer = null)
    {
        return $this->fluentlyGetOrSet('developer')->args(func_get_args());
    }

    public function count(?Closure $count = null)
    {
        return $this->fluentlyGetOrSet('count')->args(func_get_args());
    }

    public function countFor(Form $form): ?int
    {
        $callback = $this->count();

        return $callback ? $callback($form) : null;
    }

    public function component($name = null, $data = null)
    {
        return $this->fluentlyGetOrSet('component')->setter(function ($name) use ($data) {
            $this->componentData = $data;

            return $name;
        })->args(func_get_args());
    }

    public function componentData(Form $form): array
    {
        $callback = $this->componentData;

        if (! $callback) {
            return [];
        }

        return $callback($form);
    }

    public function render(Form $form): VueComponent
    {
        return VueComponent::render($this->component(), $this->componentData($form));
    }

    public function routes(?Closure $routes = null)
    {
        return $this->fluentlyGetOrSet('routes')->args(func_get_args());
    }
}
