<?php

namespace Statamic\Routing;

use Closure;
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
        return $this->fluentlyGetOrSet('slug')->getter(function ($slug) {
            if ($slug instanceof Closure) {
                $this->slug = null;
                $slug = $slug($this);
                $this->slug = $slug;
            }

            if (! $slug) {
                return null;
            }

            $lang = method_exists($this, 'site') ? $this->site()->lang() : null;

            return Str::slug($slug, '-', $lang);
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

        return $this->urlWithoutRedirect();
    }

    public function urlWithoutRedirect()
    {
        if (! $url = $this->absoluteUrlWithoutRedirect()) {
            return null;
        }

        return URL::makeRelative($url);
    }

    public function absoluteUrl()
    {
        if ($this->isRedirect()) {
            return $this->absoluteRedirectUrl();
        }

        return $this->absoluteUrlWithoutRedirect();
    }

    public function absoluteUrlWithoutRedirect()
    {
        return $this->makeAbsolute($this->uri());
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

    public function absoluteRedirectUrl()
    {
        return $this->makeAbsolute($this->redirectUrl());
    }

    private function makeAbsolute($url)
    {
        if (! $url) {
            return null;
        }

        if (! Str::startsWith($url, '/')) {
            return $url;
        }

        return URL::tidy($this->site()->absoluteUrl().'/'.$url);
    }
}
