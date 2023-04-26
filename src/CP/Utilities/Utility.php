<?php

namespace Statamic\CP\Utilities;

use Closure;
use Statamic\Http\Controllers\CP\Utilities\UtilitiesController;
use Statamic\Statamic;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Utility
{
    use FluentlyGetsAndSets;

    protected $handle;
    protected $icon;
    protected $action;
    protected $view;
    protected $viewData;
    protected $title;
    protected $navTitle;
    protected $description;
    protected $docsUrl;
    protected $routes;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function icon($icon = null)
    {
        return $this
            ->fluentlyGetOrSet('icon')
            ->setter(function ($value) {
                return Str::startsWith($value, '<svg') ? $value : Statamic::svg($value);
            })
            ->value($icon);
    }

    public function slug()
    {
        return Str::slug($this->handle);
    }

    public function action($action = null)
    {
        return $this->fluentlyGetOrSet('action')->getter(function ($action) {
            return $action ?? [UtilitiesController::class, 'show'];
        })->args(func_get_args());
    }

    public function view($view = null, $data = null)
    {
        return $this->fluentlyGetOrSet('view')->setter(function ($view) use ($data) {
            $this->viewData = $data;

            return $view;
        })->args(func_get_args());
    }

    public function viewData($request)
    {
        $callback = $this->viewData;

        if (! $callback) {
            return [];
        }

        return $callback($request);
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')->getter(function ($title) {
            return $title ?? Str::title($this->handle());
        })->args(func_get_args());
    }

    public function navTitle($title = null)
    {
        return $this->fluentlyGetOrSet('navTitle')->getter(function ($title) {
            return $title ?? $this->title();
        })->args(func_get_args());
    }

    public function description($description = null)
    {
        return $this->fluentlyGetOrSet('description')->args(func_get_args());
    }

    public function docsUrl($docsUrl = null)
    {
        return $this->fluentlyGetOrSet('docsUrl')->args(func_get_args());
    }

    public function url()
    {
        return cp_route('utilities.index').'/'.$this->slug();
    }

    public function routes(Closure $routes = null)
    {
        return $this->fluentlyGetOrSet('routes')->args(func_get_args());
    }

    /** @deprecated */
    public function register()
    {
        \Statamic\Facades\Utility::push($this);
    }
}
