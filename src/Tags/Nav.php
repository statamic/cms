<?php

namespace Statamic\Tags;

use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class Nav extends Structure
{
    public function index()
    {
        return $this->structure($this->params->get('handle', 'collection::pages'));
    }

    public function breadcrumbs()
    {
        $currentUrl = URL::makeAbsolute(URL::getCurrent());
        $url = Str::removeLeft($currentUrl, Site::current()->absoluteUrl());
        $url = Str::ensureLeft($url, '/');
        $segments = explode('/', $url);
        $segments[0] = '/';

        if (! $this->params->bool('include_home', true)) {
            array_shift($segments);
        }

        $crumbs = collect($segments)->map(function () use (&$segments) {
            $uri = URL::tidy(implode('/', $segments));
            array_pop($segments);

            return $uri;
        })->mapWithKeys(function ($uri) {
            $uri = Str::ensureLeft($uri, '/');

            return [$uri => Data::findByUri($uri, Site::current()->handle())];
        })->filter();

        if (! $this->params->bool('reverse', false)) {
            $crumbs = $crumbs->reverse();
        }

        if ($this->params->bool('trim', true)) {
            $this->content = trim($this->content);
        }

        $crumbs = $crumbs->values()->map(function ($crumb) {
            $crumb->setSupplement('is_current', URL::getCurrent() === $crumb->urlWithoutRedirect());

            return $crumb;
        });

        if (! $this->parser) {
            return $crumbs;
        }

        $output = $this->parseLoop($crumbs->toAugmentedArray());

        if ($backspaces = $this->params->int('backspace', 0)) {
            $output = substr($output, 0, -$backspaces);
        }

        return $output;
    }
}
