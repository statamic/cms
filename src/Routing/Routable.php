<?php

namespace Statamic\Routing;

use Statamic\Contracts\Routing\UrlBuilder;
use Statamic\Facades\URL;
use Statamic\Support\Str;

trait Routable
{
    protected $slug;

    abstract public function route();

    abstract public function routeData();

    public function slug($slug = null)
    {
        return $this->fluentlyGetOrSet('slug')->setter(function ($slug) {
            return Str::slug($slug);
        })->args(func_get_args());
    }

    public function uri()
    {
        if (! $route = $this->route()) {
            return null;
        }

        return app(UrlBuilder::class)->content($this)->build($route);
    }

    public function url()
    {
        if ($this->isRedirect()) {
            return $this->redirectUrl();
        }

        return URL::makeRelative($this->absoluteUrl());
    }

    public function absoluteUrl()
    {
        if ($this->isRedirect()) {
            return $this->redirectUrl();
        }

        return vsprintf('%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            ltrim($this->uri(), '/'),
        ]);
    }

    public function isRedirect()
    {
        return ($url = $this->redirectUrl())
            && $url !== 404;
    }

    public function redirectUrl()
    {
        if ($redirect = $this->value('redirect')) {
            return (new \Statamic\Routing\ResolveRedirect)($redirect, $this);
        }
    }

    public function ampUrl()
    {
        if ($this->isRedirect()) {
            return null;
        }

        return ! $this->ampable() ? null : vsprintf('%s/%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            config('statamic.amp.route'),
            ltrim($this->uri(), '/'),
        ]);
    }
}
