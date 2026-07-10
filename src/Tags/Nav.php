<?php

namespace Statamic\Tags;

use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Facades\URL;

class Nav extends Structure
{
    public function index()
    {
        return $this->structure($this->params->get('handle', 'collection::pages'));
    }

    public function breadcrumbs()
    {
        $url = URL::removeSiteUrl(URL::getCurrent());

        $segments = explode('/', $url);
        $segments[0] = '/';

        if (! $this->params->bool('include_home', true)) {
            array_shift($segments);
        }

        $crumbs = collect($segments)->map(function () use (&$segments) {
            $uri = URL::tidy(implode('/', $segments), withTrailingSlash: false);
            array_pop($segments);

            return $uri;
        })->mapWithKeys(function ($uri) {
            return [$uri => Data::findByUri($uri, Site::current()->handle())];
        })->filter();

        if (! $this->params->bool('reverse', false)) {
            $crumbs = $crumbs->reverse();
        }

        if ($this->params->bool('trim', true)) {
            $this->content = trim($this->content);
        }

        $crumbs = $crumbs->values()
            ->reject(fn ($crumb) => $crumb instanceof Taxonomy && ! view()->exists($crumb->template()))
            ->map(function ($crumb) {
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
