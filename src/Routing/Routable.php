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
            return $this->redirectUrl();
        }

        return $this->absoluteUrlWithoutRedirect();
    }

    public function absoluteUrlWithoutRedirect()
    {
        if (! $uri = $this->uri()) {
            return null;
        }

        $url = vsprintf('%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            ltrim($uri, '/'),
        ]);

        return $url === '/' ? $url : rtrim($url, '/');
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
